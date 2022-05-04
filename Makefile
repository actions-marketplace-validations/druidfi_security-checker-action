PHONY :=
PROJECT_DIR := $(dir $(lastword $(MAKEFILE_LIST)))
TAG := ghcr.io/druidfi/security-checker-action:latest

PHONY += build
build:
	docker build -f app.Dockerfile --no-cache . -t $(TAG)

PHONY += test
test: FORMAT := print_r
test: build
	docker run -it --rm -w /workspace -v $(shell pwd)/tests/repo:/workspace $(TAG) --format=$(FORMAT)

.PHONY: $(PHONY)
