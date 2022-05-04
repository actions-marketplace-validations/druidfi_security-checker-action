PHONY :=
PROJECT_DIR := $(dir $(lastword $(MAKEFILE_LIST)))

PHONY += test
test: TAG := ghcr.io/druidfi/security-checker-action:latest
test:
	docker build --no-cache . -t $(TAG)
	docker run -it --rm -w /workspace -e GITHUB_WORKSPACE=/workspace -v $(shell pwd)/tests/repo:/workspace $(TAG) check

.PHONY: $(PHONY)
