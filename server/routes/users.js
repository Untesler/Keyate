const EXPRESS = require('express');
const ROUTER = EXPRESS.Router();

const USER_CONTROLLER = require('../controllers/UserController');

ROUTER.get('/showall', USER_CONTROLLER.getData);
ROUTER.post('/signin', USER_CONTROLLER.signIn);
ROUTER.post('/register', USER_CONTROLLER.register);
ROUTER.post('/decode', USER_CONTROLLER.getDecode);
ROUTER.post('/setProfile', USER_CONTROLLER.setProfile)

module.exports = ROUTER;
