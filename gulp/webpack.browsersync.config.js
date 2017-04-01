import path from "path";
import webpack from "webpack";
import config from "./config.js";

function getReactEntries() {
  let entries = {};
  // hot reload用設定
  const devClient = [
    'webpack/hot/dev-server',
    'webpack-hot-middleware/client',
  ];
  // entrypointを動的に作成。(hot reload用設定もentrypoint毎に付加しなければならない)
  config.react.map((app_name) => {
    entries[`react_${app_name}`] = [...devClient, `./js/react/${app_name}/app.js`];
  });
  return entries;
}

export default {
  watch: true,
  devtool: '#inline-source-map',
  context: path.join(process.cwd(), config.assets_dir),
  entry: getReactEntries(),
  output: {
    path: process.cwd() + "/app/webroot/js",
    publicPath: '/js/',
    filename: '[name]_app.min.js'
  },
  module: {
    loaders: [
      {
        loaders: ['react-hot-loader', 'babel-loader'],
        test: /\.js?$/,
        exclude: /node_modules/,
        include: [
          path.join(process.cwd(), config.assets_dir)
        ],
      },
      {
        test: /\.css$/,
        loaders: ['style-loader', 'css-loader'],
      },
      {
        test: /\.json$/,
        loader: 'json-loader'
      },
    ]
  },
  plugins: [
    new webpack.ProvidePlugin({
      Promise: 'es6-promise-promise'
    }),
    new webpack.LoaderOptionsPlugin({
      debug: true
    }),
    new webpack.optimize.OccurrenceOrderPlugin(),
    new webpack.NoEmitOnErrorsPlugin(),
    new webpack.HotModuleReplacementPlugin(),
  ],
}
