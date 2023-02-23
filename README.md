# Scraper: Correos.es

> A scraper that allows to collect all the Spanish postal-codes from the official management entity.

[TOC]

## Summary

This scraper makes use of [`https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=08001`](https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=08001) as remote endpoint to check and validate auto-generated postal codes. 

## TL;DR

### Introduction

Spain is a country politically organized into 52 provinces. Each province can contain postal codes from 1 to 999.

For example:
- Álava is the province **#1**, so possible postal codes are in the following range [**01**001..**01**999]
- Madrid is the province **#28**, so possible postal codes are in the following range [**28**001..**28**999]
- Melilla is the province **#52**, so possible postal codes are in the following range [**52**001..**52**999]

> See the [Instituto Nacional de Estadística](https://www.ine.es/en/daco/daco42/codmun/cod_provincia_en.htm) for further details

### Management

[Correos.es](https://correos.es/) is a company that manages the postal code in Spain. This company has a web site in where we can validate addresses and/or postal codes using the web form located at [`https://www.correos.es/es/en/tools/codigos-postales/details`](https://www.correos.es/es/en/tools/codigos-postales/details)

If we analyze the Network tab on the Developer Tools while searching by postal code we can see this kind of requests:

```text
GET https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=08001
```

Which brings the following information about an address **and/or a postal code**:

- Name of the localization
- Latitude
- Longitude

#### Valid requests

| Key         | Value                                                        |
| ----------- | ------------------------------------------------------------ |
| URL         | [`https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=08001`](https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=08001) |
| Status Code | 200 OK                                                       |

**Contents**

```json
{
  "suggestions": [
    {
      "text": "08001, Barcelona, Barcelona, Cataluña, ESP",
      "longitude": 2.1686990270000592,
      "latitude": 41.380160001000036
    }
  ]
}
```

#### Invalid requests

| Key         | Value                                                        |
| ----------- | ------------------------------------------------------------ |
| URL         | [`https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=01999`](https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=01999) |
| Status Code | 200 OK                                                       |

**Response**

```json
{
  "code": "404",
  "message": "Not Found",
  "moreInformation": {
    "description": "Not results found.",
    "link": "www.correos.es"
  }
}
```

## Technical Aspects

### Requirements

To use this repository it is required:

- [Docker](https://www.docker.com/) - An open source containerization platform.
- [Git](https://git-scm.com/) - The free and open source distributed version control system.

### Built with

This project has been built using the following tools:

- [Dockerized PHP](https://github.com/fonil/dockerized-php) - A lighweight PHP development environment.
- [Bash](https://www.gnu.org/software/bash/) - The GNU Project's shell.
- [Sublime Text](https://www.sublimetext.com/) - Text Editing, Done Right.

### Getting Started

Just clone the repository into your preferred path:

```bash
$ mkdir -p ~/path/to/my-new-project && cd ~/path/to/my-new-project
$ git clone git@github.com:AlcidesRC/scraper-correos.git .
```

#### Commands

A *Makefile* is provided with some predefined commands:

```bash
~/path/to/my-new-project$ make

╔══════════════════════════════════════════════════════════════════════════════╗
║                                                                              ║
║                           .: AVAILABLE COMMANDS :.                           ║
║                                                                              ║
╚══════════════════════════════════════════════════════════════════════════════╝

· bash                           Starts a Bash session
· build                          Builds the service
· combine-csv                    Combine all CSV into one single file
· composer-dump                  Runs <composer dump-auto>
· composer-install               Runs <composer install>
· composer-remove                Runs <composer remove PACKAGE-NAME>
· composer-require               Runs <composer require>
· composer-require-dev           Runs <composer require-dev>
· composer-update                Runs <composer update>
· correos                        Scrapes Correos.es
· down                           Stops the service
· fix                            Fixes the source code to follow the standards
· logs                           Exposes the logs
· restart                        Restarts the service
· tests                          Runs the tests suites
· up                             Starts the service
· version                        Displays the PHP version
```

##### Build the service

```bash
~/path/to/my-new-project$ make build
```

##### Start the service

```bash
~/path/to/my-new-project$ make up
```

##### Install dependencies

```bash
~/path/to/my-new-project$ make composer-install
```

##### Scrape a province

```bash
~/path/to/my-new-project$ make correos province=1
```
> As result the file **/output/province-01.csv** is created with imported information

##### Combine all CSV files into one

```bash
~/path/to/my-new-project$ make combine-csv
```

> As result the file **/output/combined.csv** is created with the content of all CSV files present in current folder

##### Stop the service

```bash
~/path/to/my-new-project$ make down
```

##### NOTE

You can see whole list of available commands from here [Dockerized PHP README.md](https://github.com/fonil/dockerized-php/blob/main/README.md) file.

#### Security Vulnerabilities

Please review our security policy on how to report security vulnerabilities:

**PLEASE DON'T DISCLOSE SECURITY-RELATED ISSUES PUBLICLY**

#### Supported Versions

Only the latest major version receives security fixes.

### License

The MIT License (MIT). Please see [LICENSE](./LICENSE) file for more information.
