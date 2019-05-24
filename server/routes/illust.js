const EXPRESS = require('express');
const ROUTER = EXPRESS.Router();

const ILLUST_CONTROLLER = require('../controllers/IllustController');

//List all illustrations
ROUTER.get('/', ILLUST_CONTROLLER.listAllWork);
//List all work of user
ROUTER.get('/user', ILLUST_CONTROLLER.listWork);
//Submit new work
ROUTER.post('/user', ILLUST_CONTROLLER.submitWork);
//Set deleted state for this work
ROUTER.delete('/user/:illustID', ILLUST_CONTROLLER.deleteWork);
//Get work data
ROUTER.get('/:illustID', ILLUST_CONTROLLER.getWork);
//Increase the number of views
ROUTER.put('/view/:illustID', ILLUST_CONTROLLER.addView);
//Increase the number of popular and push this illustID to favorites list of user
ROUTER.put('/popular/:illustID', ILLUST_CONTROLLER.addPopular);

module.exports = ROUTER;
