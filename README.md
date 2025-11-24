Deepstone
This repository contains the Deepstone project which runs on XAMPP (C:\xampp\htdocs\deepstone). It now includes a small frontend "joke-generator" demo that fetches random jokes from the Official Joke API.

Contents

joke-generator/
index.html — simple client UI
style.css — styling
script.js — fetches a random joke from https://official-joke-api.appspot.com/random_joke and enables copy-to-clipboard
Quick start (local, XAMPP)

Place this repository in your XAMPP htdocs folder (example path: C:\xampp\htdocs\deepstone).
Start Apache from XAMPP Control Panel.
Open the joke generator in your browser: http://localhost/deepstone/joke-generator/
Development

Open the project in VS Code: code C:\xampp\htdocs\deepstone
Create feature branches for changes: git checkout -b feature/my-change
Commit and push, then open a Pull Request for review.
Notes

Do not commit secrets or local databases. Use .env (ignored) and .env.example for samples.
A basic PHP CI workflow can be added in .github/workflows to run lint/tests on push. "@ | Out-File -Encoding UTF8 README.md