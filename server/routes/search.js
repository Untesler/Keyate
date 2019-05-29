const EXPRESS = require('express');
const ROUTER  = EXPRESS.Router();

const SEARCH_CONTROLLER = require('../controllers/SearchController');

//List all illustrations
ROUTER.get('/:keyword/:tags/:categories/:orderBy/:searchType', SEARCH_CONTROLLER.fullSearch);
//Quick search, search with keyword.
ROUTER.get('/keyword/:keyword', SEARCH_CONTROLLER.quickSearch);
//List all user data order by user's rank (#followers + #famousPoints(sum of all populars point))
ROUTER.get('/rank/:nRow', SEARCH_CONTROLLER.rankSearch);
//Submit new work
ROUTER.get('/popular/:nRow', SEARCH_CONTROLLER.popularSearch);
//List all illustrations of user
ROUTER.get('/user/:uid/illusts', SEARCH_CONTROLLER.userIllusts);
//List all followers of user
ROUTER.get('/user/:uid/followers', SEARCH_CONTROLLER.userFollowers);
//List all followings of user
ROUTER.get('/user/:uid/following', SEARCH_CONTROLLER.userFollowing);
//List all illustration data for bookmark items that collect in favorite field on user account, authenicate needed.
ROUTER.get('/bookmarks', SEARCH_CONTROLLER.listBookmarks);

module.exports = ROUTER;