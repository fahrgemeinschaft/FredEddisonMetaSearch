require('./bootstrap');

import SwaggerUI from 'swagger-ui'
// or use require, if you prefer
const SwaggerUI = require('swagger-ui')

const ui = SwaggerUI({
    url: "https://petstore.swagger.io/v2/swagger.json",
    dom_id: '#swagger-ui',
    presets: [
        SwaggerUIBundle.presets.apis,
        SwaggerUIBundle.SwaggerUIStandalonePreset
    ],
    layout: "StandaloneLayout"
})
