const EXPRESS = require('express');
const ROUTER = EXPRESS.Router();

const USER_CONTROLLER = require('../controllers/UserController');

//Get user data
ROUTER.get('/', USER_CONTROLLER.getData);
ROUTER.post('/signin', USER_CONTROLLER.signIn);
ROUTER.post('/register', USER_CONTROLLER.register);
//test route
ROUTER.post('/decode', USER_CONTROLLER.getDecode);
ROUTER.put('/setProfile', USER_CONTROLLER.setProfile);
//test route
ROUTER.get('/findUserUid', USER_CONTROLLER.findUserUid);
ROUTER.get('/favorites', USER_CONTROLLER.getFavorites);
ROUTER.get('/followers', USER_CONTROLLER.getFollowers);

module.exports = ROUTER;
