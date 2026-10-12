# PALAZ ONLINE

PALAZ ONLINE — modern, responsive e-commerce platform for Palaz products and connected services.

## Stack
- Laravel 13
- PHP 8.3+
- Bootstrap 5 RTL
- Vite
- PostgreSQL

Development is being built step-by-step, starting with the desktop experience and then responsive mobile/tablet support.

## Persian voice for the AI advisor

The advisor generates spoken replies through the hosted ElevenLabs API, not through voices installed on a customer's Windows or phone. To enable audio, configure these variables in the local `.env` and in the deployed service's environment settings:

- `ELEVENLABS_API_KEY`: API key from the ElevenLabs account.
- `ELEVENLABS_VOICE_ID`: ID of a female voice that supports Persian/Farsi, selected in the ElevenLabs voice library.
- `ELEVENLABS_TTS_MODEL=eleven_v3`.

Keep the API key out of Git. The video clip is muted and is used only for motion while the generated audio plays. If the provider settings are absent or the service fails, the text response remains available.

