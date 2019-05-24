const EXPRESS = require('express');
const ROUTER = EXPRESS.Router();

const COMMENT_CONTROLLER = require('../controllers/CommentController');

//List all comments from comment box 
ROUTER.get('/:id', COMMENT_CONTROLLER.getAllComments);
//Push a comment into comment box
ROUTER.put('/:id', COMMENT_CONTROLLER.addNewComment);
//Remove comment box
ROUTER.delete('/:id', COMMENT_CONTROLLER.delCommentBox);
//Remove comment in comment box
ROUTER.delete('/:id/comment/:commentId', COMMENT_CONTROLLER.delComment);
//Vote helpful to comment
ROUTER.put('/:id/comment/:commentId', COMMENT_CONTROLLER.addHelpful);
//Get number of comments in comment box
ROUTER.get('/total/:id', COMMENT_CONTROLLER.totalComments);
//Get latest comment from comment box
ROUTER.get('/latest/:id', COMMENT_CONTROLLER.latestComment);

module.exports = ROUTER;