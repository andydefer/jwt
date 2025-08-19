.PHONY: help release add_commit tag_and_push

GIT_BRANCH=$(shell git rev-parse --abbrev-ref HEAD)

help: ## Affiche ce help.
	@echo "Utilisation :"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS=":.*?## "}; {printf "  %-20s -> %s\n", $$1, $$2}'

release: add_commit tag_and_push ## Lance une release complète et interactive.

add_commit: ## Ajoute et committe tous les fichiers modifiés et non-suivis.
	@echo "==> 🚀 Préparation de la release sur la branche '$(GIT_BRANCH)'."
	@git add -A
	@if ! git diff-index --quiet HEAD --; then \
		read -p "Message de commit de la release : " msg; \
		if [ -z "$$msg" ]; then \
			echo "Erreur : Message de commit non fourni. Annulation." && exit 1; \
		fi; \
		git commit -m "Release: $$msg"; \
		echo "Fichiers non-suivis et modifiés ont été committés."; \
	else \
		echo "Le répertoire de travail est propre, pas de commit nécessaire."; \
	fi

tag_and_push: ## Crée un tag, pousse le commit et le tag.
	@read -p "Nouvelle version (ex: 1.2.0) : " new_version; \
	if [ -z "$$new_version" ]; then \
		echo "Erreur : Version invalide. Annulation." && exit 1; \
	fi; \
	@echo "==> Création du tag v$$new_version."
	@git tag -a "v$$new_version" -m "Version $$new_version"; \
	@echo "==> Poussée du commit et du tag vers 'origin'."
	@git push origin $(GIT_BRANCH); \
	@git push origin "v$$new_version"; \
	@echo "✨ Release terminée avec succès ! Version : v$$new_version"