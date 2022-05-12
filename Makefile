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
test: FORMAT := markdown
test:
	./checker --lock=./tests/repo/composer.lock --format=$(FORMAT)

PHONY += test-no-updates
test-no-updates: FORMAT := markdown
test-no-updates:
	./checker --lock=./tests/repo_no_updates/composer.lock --format=$(FORMAT)

PHONY += test-docker
test-docker: FORMAT := print_r
test-docker: build-docker-image
	docker run -it --rm -w /workspace -v $(shell pwd)/tests/repo:/workspace $(TAG) /checker --format=$(FORMAT)

PHONY += test-gha
test-gha: FORMAT := markdown
test-gha: build-gha-image
	docker run -it --rm -w /workspace -v $(shell pwd)/tests/repo:/workspace $(TAG) /checker --format=$(FORMAT)

PHONY += update-release-v1
update-release-v1:
	git tag -d v1
	git tag v1 main
	git push --delete origin v1
	git push --tags
	gh release edit v1 --draft=false

.PHONY: $(PHONY)
