const path = require('path');
const {merge} = require('webpack-merge');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');
const RemovePlugin = require('remove-files-webpack-plugin');

const assetsPath = path.resolve(__dirname, 'assets');

const commonConfig = {
    entry: {
        'frontend-script': {
            import: './src/js/frontend/frontend-script-index.js',
            filename: './js/[name].js'
        },
        'admin-script': {
            import: './src/js/admin/admin-script-index.js',
            filename: './js/[name].js'
        },
        'elementor-team-listing': {
            import: './src/js/admin/elementor-team-listing.js',
            filename: './js/[name].js'
        },
        'frontend-style': './src/scss/frontend-style.scss',
        'admin-style': './src/scss/admin-style.scss',
        'elementor-team-listing-style': './src/scss/elementor-team-listing-style.scss'
    },

    output: {
        path: assetsPath,
    },

    module: {
        rules: [
            {
                test: /\.scss$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: [
                                    ['postcss-preset-env'],
                                ],
                            },
                        },
                    },
                    "fast-sass-loader"
                ]
            }
        ]
    },

    plugins: [
        // Prevent WebPack from generating JS files for CSS files
        new FixStyleOnlyEntriesPlugin(),

        new MiniCssExtractPlugin({
            filename: function (pathData) {
                let groups = [...pathData.chunk._groups];
                let items = groups[0].origins;
                let filename;

                if (items.length === 1) {
                    let request = items[0]['request'];
                    filename = request.match(/([^\/]+)\.(?:css|scss)$/i)[1] + '.css';
                } else {
                    filename = pathData.chunk.name + '.css';
                }

                return './css/' + filename;
            }
        }),

        new RemovePlugin({
            before: {
                root: './assets',
                include: ['./js', './css'],
            },
        }),

    ]

};


/**
 * Custom config depending on 'mode'
 *
 * @param env
 * @param argv
 * @returns {{}}
 */
module.exports = (env, argv) => {
    const mode = argv.mode || 'development';
    const config = {};

    switch (mode) {
        case 'development':
            break;

        case 'production':
            break;

        default:
            throw new Error('ERROR: Unknown mode');
    }

    return merge(commonConfig, config);

}
