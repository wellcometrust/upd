# Understanding Patient Data pattern library

Styles, static assets and markup patterns for Understanding Patient Data.

Requires Node.js >= 4.4.7.

## Quickstart

### Install

Install with Yarn or NPM:

```
yarn install
```

### Build

```
node ./scripts/build [--library-folder <path>] [--assets-folder <path>]
```

Or:

```
yarn run build -- [--library-folder <path>] [--assets-folder <path>]
```
(Note the `--` separator between the script name and arguments when running as a Yarn/NPM script.)

```
CLI Options:
  --library-folder  The folder to which the static pattern library will be
                    compiled. If omitted, a static version of the pattern
                    library will not be created.
  --assets-folder   The folder to which the static assets (images, CSS and
                    JavaScript) will be compiled. May be relative to the
                    pattern library root folder or absolute. [temp]
```

### Run a development server

If you are running a development server you don't need to run the build script. Instead, use Gulp and Webpack to watch and continuously build your assets:

```
gulp
```

And:

```
webpack --config config/webpack.config.js --watch
```

Then start the Fractal development server:

```
node ./scripts/start
```

Or:

```
yarn start
```
