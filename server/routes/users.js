const EXPRESS = require('express');
const ROUTER = EXPRESS.Router();

const USER_CONTROLLER = require('../controllers/UserController');

ROUTER.get('/', USER_CONTROLLER.getIndexPage);
ROUTER.get('/getall', USER_CONTROLLER.getData);

module.exports = ROUTER;
