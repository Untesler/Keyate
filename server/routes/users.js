const EXPRESS = require('express');
const ROUTER = EXPRESS.Router();

const USER_CONTROLLER = require('../controllers/UserController');

//Get user data
ROUTER.get('/', USER_CONTROLLER.getData);
// sign in
ROUTER.post('/signin', USER_CONTROLLER.signIn);
// register new user
ROUTER.post('/register', USER_CONTROLLER.register);
// update user information
ROUTER.put('/setProfile', USER_CONTROLLER.setProfile);
// get all favorites of user
ROUTER.get('/favorites', USER_CONTROLLER.getFavorites);
// get all followers of user
ROUTER.get('/followers', USER_CONTROLLER.getFollowers);
// verify token, return user data as json when has no mistake if not will return false
ROUTER.post('/verify', USER_CONTROLLER.verify);

module.exports = ROUTER;
