#!/bin/bash

# Lancer PHP-FPM en arrière-plan
php-fpm &

# Lancer Nginx en mode non-daemon (au premier plan)
nginx -g 'daemon off;'

# Attendre que les processus se terminent
wait -n

# Si l'un des processus se termine, arrêter le container
exit $?
