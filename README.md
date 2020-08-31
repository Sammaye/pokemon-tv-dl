# pokemon-tv-dl

Allows for downloading blob data form Pokemon TV; yes - essential life stuff.

## Usage

- Find an episode like https://watch.pokemon.com/en-gb/player.html?id=295ef5f60e734937986a83e9a071bc28
- Open Chrome Dev Tools (or whatever you use)
- Search for playlist0.ts under the XHR sub tab of the Network tab
- Copy the URL it shows there
- Paste that URL into urls.txt (and more if you have them, one on each line)
- Run download.php

The script will then download the parts and then use ffmpeg to join them into episodes in the complete folder.
