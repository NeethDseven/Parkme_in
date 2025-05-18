@echo off
echo Initialisation du depot Git...
git init

echo Configuration du depot distant...
git remote add origin https://github.com/NeethDseven/Parkme_in.git

echo Ajout des fichiers au suivi Git...
git add .

echo Creation du commit initial...
set /p commit_message="Entrez votre message de commit: "
git commit -m "%commit_message%"

echo Passage a la branche principale...
git branch -M main

echo Envoi du code vers GitHub...
git push -u origin main

echo Terminé!
pause
