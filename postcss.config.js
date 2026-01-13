module.exports = {
  plugins: [
    require('postcss-import')({ path: ['assets/css'] }), // @import パス解決
    require('autoprefixer')(),                           // ベンダープレフィックス付与
    require('cssnano')({ preset: 'default' })            // 圧縮
  ],
  map: { inline: false, annotation: true }
};