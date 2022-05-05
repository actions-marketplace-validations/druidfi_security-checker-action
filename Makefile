PHONY :=
PROJECT_DIR := $(dir $(lastword $(MAKEFILE_LIST)))
TAG := ghcr.io/druidfi/security-checker-action:latest

PHONY += build-docker-image
build-docker-image:
	docker build -f app.Dockerfile --no-cache . -t $(TAG)

PHONY += build-gha-image
build-gha-image: build-docker-image
	docker build --no-cache . -t $(TAG)

PHONY += test
test: FORMAT := print_r
test: build-docker-image
	docker run -it --rm -w /workspace -v $(shell pwd)/tests/repo:/workspace $(TAG) /checker --format=$(FORMAT)

PHONY += test-gha
test-gha: FORMAT := markdown
test-gha: build-gha-image
	docker run -it --rm -w /workspace -v $(shell pwd)/tests/repo:/workspace $(TAG) /checker --format=$(FORMAT)

.PHONY: $(PHONY)
