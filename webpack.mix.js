let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js(	'resources/assets/js/admin_app.js','public/js')
	.extract([
		'axios',
		'vue',
		'vue-moment',
		'vuejs-datepicker',
		'vue-router',
		'vuejs-dialog',
		'vue-resource',
		'@fortawesome/vue-fontawesome',
		'vue-debounce',
		])
	.sourceMaps();
// mix.js('resources/assets/js/login_app.js','public/js').sourceMaps();
mix.sass('resources/assets/sass/app.scss', 'public/css')
   .browserSync({
   		proxy: 'myhg.local'
   });