# webfont-generator
**Converts one OTF/TTF, SVG, SFD, WOFF and WOFF2 and makes a webfont pack.**

Built thanks to:

* [fontforge](http://fontforge.github.io/), for converting to ttf and svg. You only need to setup command line scripting tool.
* [ttf2eot](https://github.com/wget/ttf2eot)
* [sfnt2woff](https://github.com/kseo/sfnt2woff)
* [woff2_compress](https://github.com/google/woff2)
* [fonttools (pyftsubset)](https://github.com/fonttools/fonttools#other-tools)

![Webfont generator screenshot](/screenshot@2x.png)

## Usage with Docker

```bash
docker run -ti --name "webfontgen" -p 8080:80 ambroisemaupate/webfontgenerator
```

Then open your browser on `http://localhost:8080`, upload your OTF/TTF font file and… enjoy!

## Development

Clone this repository, then:

```bash
cp config.docker.yml config.yml
composer install
docker-compose up
```

Then open your browser on `http://localhost:8080`
