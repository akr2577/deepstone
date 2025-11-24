const API_URL = 'https://official-joke-api.appspot.com/random_joke';

const jokeEl = document.getElementById('joke');
const statusEl = document.getElementById('status');
const getBtn = document.getElementById('getJoke');
const copyBtn = document.getElementById('copyJoke');

async function fetchJoke() {
  try {
    setLoading(true, 'Fetching a joke...');
    const res = await fetch(API_URL, { cache: 'no-store' });
    if (!res.ok) throw new Error(`API returned ${res.status}`);
    const data = await res.json();
    const text = data.setup && data.punchline
      ? `${data.setup}\n\n${data.punchline}`
      : JSON.stringify(data);
    jokeEl.textContent = text;
    statusEl.textContent = 'Here you go!';
  } catch (err) {
    console.error(err);
    statusEl.textContent = `Failed to load joke: ${err.message}`;
  } finally {
    setLoading(false);
  }
}

function setLoading(isLoading, message = '') {
  getBtn.disabled = isLoading;
  copyBtn.disabled = isLoading;
  statusEl.textContent = message;
}

async function copyJokeToClipboard() {
  const text = jokeEl.textContent.trim();
  if (!text) {
    statusEl.textContent = 'Nothing to copy yet.';
    return;
  }
  try {
    await navigator.clipboard.writeText(text);
    statusEl.textContent = 'Copied to clipboard!';
  } catch (err) {
    try {
      const ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      statusEl.textContent = 'Copied to clipboard (fallback).';
    } catch (e) {
      statusEl.textContent = 'Copy failed.';
    }
  }
}

getBtn.addEventListener('click', fetchJoke);
copyBtn.addEventListener('click', copyJokeToClipboard);

window.addEventListener('DOMContentLoaded', () => {
  fetchJoke();
});
