# ![SlipDb](~/slipdb.png)

![Build Status](https://travis-ci.com/slipsoft/slipdb.svg?branch=master)
![Coverage Status](https://coveralls.io/repos/github/slipsoft/slipdb/badge.svg?branch=master)

A distributed, index based, search engine.

[github](https://github.com/slipsoft/slipdb)

## Getting Started

These instructions will get you a copy of the project up and running on your
local machine for development and testing purposes. See deployment for notes on
how to deploy the project on a live system.

### Prerequisites

-   JDK >= 8.x
-   Maven

### Installing

1.  Install dependencies

        mvn install

2.  Run the server

        mvn jetty:run

You can now connect to the API at http://localhost/api/test

## Running the tests

JUnit is used for the tests. You can run it with Maven:

    mvn test

## Deployment

_not deployable yet..._

## Documentation

### API

The API documentation can be found [here](https://slipsoft.github.io/slipdb/).

## Built With

-   [Maven](https://maven.apache.org/) - Dependency Management
-   [Jetty](https://www.eclipse.org/jetty/) - HTTP Server
-   [JBoss RestEasy](https://resteasy.github.io/) - RestFull Framework
-   [swagger-maven-plugin](https://github.com/kongchen/swagger-maven-plugin) -
    OpenApi doc generation
-   [swagger-ui](https://github.com/swagger-api/swagger-ui) - Web view for the
    API documentation

## Authors

-   **Sylvain JOUBE** - [SylvainLune](https://github.com/SylvainLune)
-   **Etienne LELOUËT** - [etienne-lelouet](https://github.com/etienne-lelouet)
-   **Nicolas PEUGNET** - [n-peugnet](https://github.com/n-peugnet)

See also the list of [contributors](https://github.com/slipsoft/slipdb/contributors)
who participated in this project.
