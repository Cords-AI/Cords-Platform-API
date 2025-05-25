function prefix() {
  if [ "$ENV_TYPE" = "dev" ]; then
    ENV_PREFIX="";
  else
    ENV_PREFIX="$ENV_TYPE.";
  fi
  echo $ENV_PREFIX
}

function env_base_url() {
  if [ "$ENV_TYPE" = "dev" ]; then
    ENV_BASE_URL="$1.$BASE_URL";
  else
    ENV_BASE_URL="$ENV_PREFIX.$1.$BASE_URL";
  fi
  echo $ENV_BASE_URL
}
