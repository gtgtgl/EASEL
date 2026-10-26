#!/usr/bin/env bash
# Offline test: SSH/SCP are mocks. No real host is contacted.
set -euo pipefail
script_dir=$(cd -- "$(dirname -- "$0")" && pwd)
deploy_script="$script_dir/../.github/scripts/deploy-release.sh"
temp_root=$(cd "${TMPDIR:-/tmp}" && pwd -P)
fixture=$(mktemp -d "$temp_root/easel-release-tests.XXXXXX")
case "$fixture" in "$temp_root"/easel-release-tests.*) ;; *) exit 1 ;; esac
trap 'rm -rf -- "$fixture"' EXIT
mkdir "$fixture/bin" "$fixture/artifacts"
export TEST_LOG="$fixture/log"
cat > "$fixture/bin/ssh" <<'MOCK'
#!/usr/bin/env bash
set -eu
echo ssh >> "$TEST_LOG"
while [[ "$1" != sh ]]; do shift; done
shift
sh "$@"
MOCK
cat > "$fixture/bin/scp" <<'MOCK'
#!/usr/bin/env bash
set -eu
source_file="${@: -2:1}"
destination="${@: -1}"
echo "scp:$(basename "$source_file")" >> "$TEST_LOG"
if [[ "${FAIL_JSON:-0}" == 1 && "$source_file" == */theme-update.json ]]; then exit 1; fi
cp "$source_file" "${destination#*:}"
if [[ "${CORRUPT_ZIP:-0}" == 1 && "$source_file" == */easel.zip ]]; then echo corrupt >> "${destination#*:}"; fi
MOCK
cat > "$fixture/bin/mv" <<'MOCK'
#!/usr/bin/env bash
set -eu
echo "mv:$(basename "${@: -1}")" >> "$TEST_LOG"
/usr/bin/mv "$@"
MOCK
chmod +x "$fixture/bin/ssh" "$fixture/bin/scp" "$fixture/bin/mv"
# Git Bash cannot reliably set POSIX directory modes on NTFS. Production uses
# FreeBSD mkdir -m; only the Windows mock filesystem omits that mode argument.
if [[ "$OSTYPE" == msys* || "$OSTYPE" == cygwin* ]]; then
  cat > "$fixture/bin/mkdir" <<'MOCK'
#!/usr/bin/env bash
if [[ "${1:-}" == -m ]]; then shift 2; fi
/usr/bin/mkdir "$@"
MOCK
  chmod +x "$fixture/bin/mkdir"
fi
export PATH="$fixture/bin:$PATH"
export SAKURA_SSH_HOST=example.invalid SAKURA_SSH_USER=fixture SAKURA_SSH_PORT=22
export SAKURA_SSH_PRIVATE_KEY=offline-dummy SAKURA_SSH_KNOWN_HOSTS=offline-dummy
export GITHUB_RUN_ID=123 GITHUB_RUN_ATTEMPT=1
printf 'new zip' > "$fixture/artifacts/easel.zip"
printf '{"version":"1.7"}' > "$fixture/artifacts/theme-update.json"
(cd "$fixture/artifacts" && sha256sum --text easel.zip theme-update.json > SHA256SUMS)
for scenario in success bsd-success upload-failure corruption invalid-path missing-secret local-corruption; do
  export SAKURA_DEPLOY_PATH="$fixture/$scenario"
  mkdir "$SAKURA_DEPLOY_PATH"
  printf 'old zip' > "$SAKURA_DEPLOY_PATH/easel.zip"
  printf 'old json' > "$SAKURA_DEPLOY_PATH/theme-update.json"
  : > "$TEST_LOG"
  export FAIL_JSON=0 CORRUPT_ZIP=0
  [[ "$scenario" != upload-failure ]] || export FAIL_JSON=1
  [[ "$scenario" != corruption ]] || export CORRUPT_ZIP=1
  [[ "$scenario" != invalid-path ]] || export SAKURA_DEPLOY_PATH="$fixture/../unsafe"
  [[ "$scenario" != missing-secret ]] || export SAKURA_SSH_HOST=''
  if [[ "$scenario" == bsd-success ]]; then
    cat > "$fixture/bin/sha256" <<'MOCK'
#!/usr/bin/env bash
[[ "$1" == -q ]]
sha256sum "$2" | awk '{print $1}'
MOCK
    chmod +x "$fixture/bin/sha256"
  fi
  if [[ "$scenario" == local-corruption ]]; then
    export SAKURA_SSH_HOST=example.invalid
    printf 'tampered' >> "$fixture/artifacts/easel.zip"
  fi
  if bash "$deploy_script" "$fixture/artifacts" > "$fixture/output" 2>&1; then
    [[ "$scenario" == *success ]]
    cmp "$fixture/artifacts/easel.zip" "$SAKURA_DEPLOY_PATH/easel.zip"
    cmp "$fixture/artifacts/theme-update.json" "$SAKURA_DEPLOY_PATH/theme-update.json"
    [[ "$(grep '^mv:' "$TEST_LOG")" == $'mv:easel.zip\nmv:theme-update.json' ]]
  else
    if [[ "$scenario" == *success ]]; then cat "$fixture/output" >&2; fi
    [[ "$scenario" != *success ]]
    if [[ "$scenario" == invalid-path || "$scenario" == missing-secret || "$scenario" == local-corruption ]]; then
      [[ ! -s "$TEST_LOG" ]]
    else
      [[ "$(cat "$SAKURA_DEPLOY_PATH/easel.zip")" == 'old zip' ]]
      [[ "$(cat "$SAKURA_DEPLOY_PATH/theme-update.json")" == 'old json' ]]
      ! grep -q '^mv:' "$TEST_LOG"
    fi
  fi
done
echo 'Offline deployment checks passed: ZIP-before-JSON, failed upload, corrupt transfer, invalid configuration; no network used.'
