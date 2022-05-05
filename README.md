# security-checker-action

This action checks your `composer.lock` for known vulnerabilities in your package dependencies.

Inputs
------

* `lock` *optional* The path to the `composer.lock` file (defaults to the repository root directory).
* `format` *optional* The output format (defaults to `json`, supported: `markdown`, `json`, `print_r`, or `yaml`).

Outputs
-------

* `updates` A JSON payload containing all detected security updates.

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

Test code with example `composer.lock` in `tests/repo`:

```
make test
```

Test Docker image with example `composer.lock` in `tests/repo`:

```
make test-docker
```

Test Github Action image with example `composer.lock` in `tests/repo`:

```
make test-docker
```

Example: Check some Drupal repository:

```
docker pull ghcr.io/druidfi/security-checker-action
docker run -it --rm -w /workspace -v $(pwd):/workspace ghcr.io/druidfi/security-checker-action /checker --format=markdown
```
