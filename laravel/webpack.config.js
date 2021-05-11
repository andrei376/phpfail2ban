const path = require('path');

module.exports = {
    resolve: {
        modules: [
            __dirname,
            'node_modules',
            'resources/js',
            'vendor',
        ],
        alias: {
            '@': path.resolve('resources/js'),
        },
    },
};
