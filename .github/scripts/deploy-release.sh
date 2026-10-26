#!/usr/bin/env bash
set -euo pipefail

artifact_dir="${1:?Usage: deploy-release.sh artifact-directory}"
for setting in SAKURA_SSH_HOST SAKURA_SSH_USER SAKURA_SSH_PORT SAKURA_SSH_PRIVATE_KEY SAKURA_SSH_KNOWN_HOSTS SAKURA_DEPLOY_PATH; do
  if [[ -z "${!setting:-}" ]]; then
    printf 'Missing required Secret: %s\n' "$setting" >&2
    exit 1
  fi
done
[[ "$SAKURA_SSH_HOST" =~ ^[A-Za-z0-9][A-Za-z0-9.-]*$ ]] || { echo 'Invalid SSH host' >&2; exit 1; }
[[ "$SAKURA_SSH_USER" =~ ^[A-Za-z0-9_][A-Za-z0-9_-]*$ ]] || { echo 'Invalid SSH user' >&2; exit 1; }
[[ "$SAKURA_SSH_PORT" =~ ^[0-9]{1,5}$ ]] && (( 10#$SAKURA_SSH_PORT >= 1 && 10#$SAKURA_SSH_PORT <= 65535 )) || { echo 'Invalid SSH port' >&2; exit 1; }
[[ "$SAKURA_DEPLOY_PATH" =~ ^/[A-Za-z0-9_./-]+$ && "$SAKURA_DEPLOY_PATH" != / && "$SAKURA_DEPLOY_PATH" != */ ]] || { echo 'Use an absolute deployment directory without spaces or a trailing slash' >&2; exit 1; }
[[ "/$SAKURA_DEPLOY_PATH/" != */../* && "/$SAKURA_DEPLOY_PATH/" != */./* ]] || { echo 'Dot segments are not allowed in deployment paths' >&2; exit 1; }
[[ "${GITHUB_RUN_ID:-}" =~ ^[0-9]+$ && "${GITHUB_RUN_ATTEMPT:-}" =~ ^[0-9]+$ ]] || { echo 'Missing GitHub run identity' >&2; exit 1; }

# Require precisely the expected manifest before any network operation.
[[ $(wc -l < "$artifact_dir/SHA256SUMS") -eq 2 ]]
grep -Eq '^[a-f0-9]{64}  easel\.zip$' "$artifact_dir/SHA256SUMS"
grep -Eq '^[a-f0-9]{64}  theme-update\.json$' "$artifact_dir/SHA256SUMS"
(cd "$artifact_dir" && sha256sum --check SHA256SUMS)
zip_hash=$(awk '$2 == "easel.zip" {print $1}' "$artifact_dir/SHA256SUMS")
json_hash=$(awk '$2 == "theme-update.json" {print $1}' "$artifact_dir/SHA256SUMS")

ssh_dir=$(mktemp -d)
trap 'rm -f "$ssh_dir/key" "$ssh_dir/known_hosts"; rmdir "$ssh_dir"' EXIT
chmod 700 "$ssh_dir"
printf '%s\n' "$SAKURA_SSH_PRIVATE_KEY" | tr -d '\r' > "$ssh_dir/key"
printf '%s\n' "$SAKURA_SSH_KNOWN_HOSTS" | tr -d '\r' > "$ssh_dir/known_hosts"
chmod 600 "$ssh_dir/key" "$ssh_dir/known_hosts"
ssh_options=(-i "$ssh_dir/key" -o BatchMode=yes -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes -o "UserKnownHostsFile=$ssh_dir/known_hosts" -o ConnectTimeout=20)
target="$SAKURA_SSH_USER@$SAKURA_SSH_HOST"
stage="$SAKURA_DEPLOY_PATH/.easel-release-$GITHUB_RUN_ID-$GITHUB_RUN_ATTEMPT"

# All remote arguments are restricted to shell-safe characters above.
ssh "${ssh_options[@]}" -p "$SAKURA_SSH_PORT" "$target" sh -s -- "$SAKURA_DEPLOY_PATH" "$stage" <<'REMOTE'
set -eu
test -d "$1" && test -w "$1"
mkdir -m 700 "$2"
REMOTE
# -O supports Sakura's legacy SCP service as well as modern OpenSSH servers.
scp -O "${ssh_options[@]}" -P "$SAKURA_SSH_PORT" "$artifact_dir/easel.zip" "$target:$stage/easel.zip"
scp -O "${ssh_options[@]}" -P "$SAKURA_SSH_PORT" "$artifact_dir/theme-update.json" "$target:$stage/theme-update.json"

ssh "${ssh_options[@]}" -p "$SAKURA_SSH_PORT" "$target" sh -s -- "$SAKURA_DEPLOY_PATH" "$stage" "$zip_hash" "$json_hash" <<'REMOTE'
set -eu
checksum() {
  if command -v sha256 >/dev/null 2>&1; then
    sha256 -q "$1"
  else
    sha256sum "$1" | awk '{print $1}'
  fi
}
test "$(checksum "$2/easel.zip")" = "$3"
test "$(checksum "$2/theme-update.json")" = "$4"
chmod 644 "$2/easel.zip" "$2/theme-update.json"
# Staging and final files are on the same filesystem. Never expose a partial ZIP.
mv -f "$2/easel.zip" "$1/easel.zip"
# Only announce the update after the complete ZIP is available.
mv -f "$2/theme-update.json" "$1/theme-update.json"
rmdir "$2"
REMOTE
echo 'Published easel.zip, then theme-update.json.'
