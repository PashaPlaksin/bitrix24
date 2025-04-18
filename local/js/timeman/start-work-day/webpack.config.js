const path = require('path');

module.exports = {
    entry: './src/start-work-day.js', // Путь к исходному файлу
    output: {
        filename: 'start-work-day.bundle.js', // Имя бандла
        path: path.resolve(__dirname, 'dist'), // Папка для вывода
    },
    module: {
        rules: [
            {
                test: /\.js$/, // Обрабатываем все файлы с расширением .js
                exclude: /node_modules/, // Исключаем папку node_modules
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
                    },
                },
            },
            {
                test: /\.js$/,
                enforce: 'pre',
                loader: 'source-map-loader', // Загружаем source maps для отладки
            },
        ],
    },
    devtool: 'source-map', // Генерация source map
    mode: 'production', // Используем режим сборки для продакшн
};
