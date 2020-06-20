const path = require( 'path' );
const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );

const webPackModule = ( production = true ) => {
	return {
		rules: [
			{
				loader: 'babel-loader',
				test: /\.js$/,
				exclude: /node_modules/,
				query: {
					presets: [ 'env' ],
				},
			},
			{
				test: /\.s?css$/,
				use: ExtractTextPlugin.extract( {
					fallback: 'style-loader',
					use: [
						{
							loader: 'css-loader',
							options: {
								sourceMap: ! production,
								minimize: production,
							},
						},
						{
							loader: 'sass-loader',
							options: {
								sourceMap: ! production,
							},
						},
						{
							loader: 'postcss-loader',
						},
					],
				} ),
			},
		],
	};
};

const front = ( env ) => {
	const isProduction = 'production' === env;

	return {
		entry: [ 'cross-fetch', './src/js/front/app.js' ],
		output: {
			path: path.join( __dirname, 'assets', 'js' ),
			filename: path.join( 'front', 'app.js' ),
		},
		module: webPackModule( ! isProduction ),
		plugins: [ new ExtractTextPlugin( path.join( 'css', 'sample.css' ) ) ],
		devtool: isProduction ? '' : 'inline-source-map',
	};
};

const admin = ( env ) => {
	const isProduction = 'production' === env;

	return {
		entry: [ 'cross-fetch', './src/js/admin/app.js' ],
		output: {
			path: path.join( __dirname, 'assets', 'js' ),
			filename: path.join( 'admin', 'app.js' ),
		},
		module: webPackModule( ! isProduction ),
		plugins: [ new ExtractTextPlugin( path.join( 'css', 'sample.css' ) ) ],
		devtool: isProduction ? '' : 'inline-source-map',
	};
};

module.exports = [ front, admin ];
