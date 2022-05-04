# security-checker-action

This action checks your `composer.lock` for known vulnerabilities in your package dependencies.

Inputs
------

* `lock` *optional* The path to the `composer.lock` file (defaults to the repository root directory).
* `format` *optional* The output format (defaults to `json`, supported: `markdown`, `json`, or `yaml`).

Outputs
-------

* `vulns` A JSON payload containing all detected vulnerabilities

Usage
-----

If you want the step to fail whenever there is a security issue in one of your
dependencies, use this action:

    steps:
        - uses: actions/checkout@v3
        - uses: druidfi/security-checker-action@v1

To speed up security checks, you can cache the vulnerability database:

    steps:
        - uses: actions/checkout@v3
        - uses: actions/cache@v2
          id: cache-db
          with:
              path: ~/.symfony/cache
              key: db
        - uses: druidfi/security-checker-action@v1

If the `composer.lock` is not in the repository root directory, pass is as an
input:

    steps:
        - uses: actions/checkout@v3
        - uses: druidfi/security-checker-action@v1
          with:
              lock: subdir/composer.lock

## Development

Build and test example `composer.lock` in `tests/repo`:

```
make test
```

Create Github Action Docker image:

```
docker build --no-cache --progress plain . -t ghcr.io/druidfi/security-checker-action:latest
```

Run in some Drupal repo folder:

```
docker run -it --rm -w /workspace -v $(pwd):/workspace ghcr.io/druidfi/security-checker-action:latest
```
