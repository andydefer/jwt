# ================================
# Makefile Release Automatique (Laravel)
# ================================

GIT_BRANCH=$(shell git branch --show-current)
VERSION_FILE=VERSION

help: ## Affiche ce help
	@echo "Usage :"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS=":.*?## "}; {printf "  %-15s -> %s\n", $$1, $$2}'

release: ## Lancer une release interactive
	@echo "==> Release interactive pour $(GIT_BRANCH)"
	@if ! git diff-index --quiet HEAD --; then \
		echo "Erreur : working directory n'est pas propre. Committez vos changements."; \
		exit 1; \
	fi
	@read -p "Message du commit : " msg; \
	read -p "Nouvelle version (ex: 1.2.0) : " new_version; \
	if [ -z "$$new_version" ]; then \
		echo "Erreur : version invalide"; exit 1; \
	fi; \
	echo "$$new_version" > $(VERSION_FILE); \
	git add .; \
	git commit -m "$$msg"; \
	git tag -a "v$$new_version" -m "$$msg"; \
	git push origin $(GIT_BRANCH); \
	git push origin "v$$new_version"; \
	echo "Release terminée avec succès ! Version actuelle : $$new_version"
