const webpack = require("webpack");
const path = require("path");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

module.exports = {
    entry: {
        frontend: path.resolve(__dirname, "./js/index.js"),
        push: path.resolve(__dirname,  "./js/Firebase/PushNotifications.js"),
        'firebase-messaging-sw': path.resolve(__dirname,  "./js/PWA/FirebaseServiceWorker.js"),
    },
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: ["babel-loader"],
            }
        ],

    },
    resolve: {
        extensions: ["*", ".js", ".jsx"],
    },
    output: {
        path: path.resolve(__dirname, "./public/js"),
        filename: "[name].js",
    },
    plugins: [
        //new webpack.optimize.AggressiveMergingPlugin(),//Merge chunks
        //new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
        new CleanWebpackPlugin(),
    ],
    mode: 'production',
    optimization: {
        minimizer: [new UglifyJsPlugin()],
        //namedModules: false,
        //namedChunks: false,
        nodeEnv: 'production',
        flagIncludedChunks: true,
        //occurrenceOrder: true,
        sideEffects: true,
        usedExports: true,
        concatenateModules: true,
        noEmitOnErrors: true,
        checkWasmTypes: true,
        minimize: true,
    },
    //devtool: 'source-map'
};
