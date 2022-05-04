# security-checker-action

## Inputs

## `who-to-greet`

**Required** The name of the person to greet. Default `"World"`.

## Outputs

## `time`

The time we greeted you.

## Example usage

uses: druidfi/hello-world-docker-action@v1
with:
  who-to-greet: 'Mona the Octocat'

## Requirements for development

- PHP 8.1
- Docker

Get Drupal data:

```
./checker drupal:data
```

Create Github Action Docker image:

```
docker build --no-cache --progress plain . -t ghcr.io/druidfi/security-checker-action:latest
```

Run Docker container:

```
docker run -it --rm -w /workspace -e GITHUB_WORKSPACE=/workspace -v $(pwd)/tests/repo:/workspace ghcr.io/druidfi/security-checker-action:latest [command]
```

Run in some Drupal repo folder:

```
docker run -it --rm -w /workspace -e GITHUB_WORKSPACE=/workspace -v $(pwd):/workspace ghcr.io/druidfi/security-checker-action:latest check
```
