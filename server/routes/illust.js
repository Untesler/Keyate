const EXPRESS = require('express');
const ROUTER = EXPRESS.Router();

const ILLUST_CONTROLLER = require('../controllers/IllustController');

//Submit new work
ROUTER.post('/work', ILLUST_CONTROLLER.submitWork);
//Set deleted state for this work
ROUTER.delete('/work', ILLUST_CONTROLLER.deleteWork);
//Get work data
ROUTER.get('/work', ILLUST_CONTROLLER.getWork);
//Count viewer
ROUTER.put('/view', ILLUST_CONTROLLER.addView);
//Count popular from favorite btn
ROUTER.put('/popular', ILLUST_CONTROLLER.addPopular);

module.exports = ROUTER;
