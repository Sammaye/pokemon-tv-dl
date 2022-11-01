# pokemon-tv-dl

Allows for downloading blob data from Pokemon TV; yes - essential life stuff.

## Usage

- Install Docker Desktop
- Clone this repository to a folder on your computer
- Open a terminal, like PowerShell
- Type: `cd /path/to/repo` and press enter
- Type: `docker compose up` and press enter
- Go to the Pokemon TV site
- Find an episode like https://watch.pokemon.com/en-gb/player.html?id=295ef5f60e734937986a83e9a071bc28
- Open Chrome Dev Tools (or whatever you use)
- Search for playlist0.ts under the XHR sub tab of the Network tab
- Copy the URL it shows there
- Paste that URL into urls.txt (and more if you have them, one on each line)
- Go to a shell, like PowerShell
- Type: `docker exec -it pokemon_tv_dl_app bash` and press enter
- Type: `cd /var/www/html` and press enter
- Type: `php ./download.php` and press enter
- It will now download your episodes from the urls.txt file

The script will then download the parts and then use ffmpeg to join them into episodes in the complete folder.

## Torubleshooting

If you get errors while runnning download.php it could be because it didn't install the PHP application right:
- Open a new shell, like PowerShell
- Type: `docker exec -it pokemon_tv_dl_app bash` and press enter
- Type: `cd /var/www/html` and press enter
- Type: `composer install` and press enter
- Wait for it to complete

After that it should fix itself, try running download.php again.
