# ================================
# Makefile Release Automatique
# ================================

GIT_BRANCH=$(shell git branch --show-current)

help: ## Affiche ce help
	@echo "Usage :"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS=":.*?## "}; {printf "  %-15s -> %s\n", $$1, $$2}'

release: ## Lancer une release interactive
	@echo "==> Release interactive pour $(GIT_BRANCH)"
	@read -p "Message du commit : " msg; \
	read -p "Type de version (major/minor/patch) : " type; \
	if [ "$$type" != "major" ] && [ "$$type" != "minor" ] && [ "$$type" != "patch" ]; then \
	    echo "Erreur : type invalide"; exit 1; \
	fi; \
	git add .; \
	git commit -m "$$msg"; \
	npm version $$type; \
	git push origin $(GIT_BRANCH) --follow-tags; \
	echo "Release terminée avec succès ! Version actuelle : $$(node -p "require('./package.json').version")"
