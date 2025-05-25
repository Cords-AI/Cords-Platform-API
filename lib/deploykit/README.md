# DeployKit

DeployKit is a helper for local and remote deployments.  

```
git submodule add git@github.com:$REPOSITORY.git lib/deploykit
git submodule update --init
```

## Remote Deployments

DeployKit deploys the repository, creates a Github deployment, and creates a Github release.

```bash
cp lib/deploykit/deploy.example.yml .github/workflows/deploy.yml
```

## Hooks

**Preflight Hook**

```bash
# filename: hooks/deploykit-preflight

export HELLO=WORLD
```

**Environment Specific Preflight Hook**

```
# filename: hooks/deploykit-preflight.$ENV_NAME # dev, demo, prod...
```

**Deploy Hook**

```bash
# filename: hooks/deploykit-deploy

rsync . /var/www/html
```

## Local deployments

Creates a devcontainer

```bash
./lib/deploykit/devcontainer/devup
```

## Hooks

```bash
# filename: hooks/devup-init

export HELLO=WORLD
```
