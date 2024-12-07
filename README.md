# Louka-Loca

## configuration de l'environement  

- Copiez le fichier `.env.example` vers `.env` :

```bash
cp .env.example .env
```

- Modifiez les paramètres nécessaires dans le fichier `.env` :
  - **Database** : Configurez les variables `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, et `DB_PASSWORD` en fonction de votre configuration.
  - **Traefik** : Assurez-vous que le domaine `APP_URL` correspond à celui configuré dans Traefik pour cet exemple d'application Laravel.

- Utilisez Docker Compose pour démarrer l'application:

```bash
docker compose -f docker-compose.prod.yaml up --build -d
```

↑ pour de la prod et ↓ pour la dev

```bash
docker compose up --build -d
```
