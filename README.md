# security-checker-action

## Requirements for development

- PHP 8.1
- Docker

Get Drupal data

```
./checker drupal:data
```

Create Github Action Docker image:

```
docker build --no-cache --progress plain . -t druidfi/security-checker-action
```

Run Docker container:

```
docker run -it --rm druidfi/security-checker-action
```
