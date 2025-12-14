const path = require("path");

module.exports = {
  productionSourceMap: process.env.NODE_ENV === 'production' ? false : true,

  css: {
    extract: true, // ✅ forces CSS file output even in dev builds
    loaderOptions: {
      sass: {
        additionalData: `
          @import "@/scss/_mixins.scss";
          @import "@/scss/_functions.scss";
          @import "@/scss/_variables.scss";
        `
      }
    }
  },

  filenameHashing: false,

  publicPath: '/wp-content/plugins/cookie-law-info/lite/admin/dist',

  transpileDependencies: [
    '@wordpress/hooks',
    '@wordpress/i18n'
  ],

  configureWebpack: (config) => {
    config.module.rules.push({
      test: /\.m?js$/,
      include: /node_modules\/@wordpress/,
      use: {
        loader: 'babel-loader',
        options: {
          presets: [
            ['@babel/preset-env', { targets: { node: 'current' } }]
          ],
          plugins: [
            '@babel/plugin-proposal-optional-chaining',
            '@babel/plugin-proposal-nullish-coalescing-operator'
          ]
        }
      }
    });
  },

  chainWebpack: (config) => {
    const isProd = process.env.NODE_ENV === 'production';

    config.output
      .filename(isProd ? 'js/[name].min.js' : 'js/[name].js')
      .chunkFilename(isProd ? 'js/[name].min.js' : 'js/[name].js');

    if (config.plugins.has('extract-css')) {
      config.plugin('extract-css').tap(args => {
        args[0].filename = isProd ? 'css/[name].min.css' : 'css/[name].css';
        args[0].chunkFilename = isProd ? 'css/[name].min.css' : 'css/[name].css';
        return args;
      });
    }
  }
};