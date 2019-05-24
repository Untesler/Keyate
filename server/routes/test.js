const EXPRESS = require("express");
const ROUTER = EXPRESS.Router();
const PATH = require("path");

ROUTER.get('/', (req, res) => {
    res.sendFile(PATH.join(__dirname, "..") + "/test/login.html");
});

ROUTER.get('/login', (req, res) => {
    res.sendFile(PATH.join(__dirname, "..") + "/test/login.html");
});

ROUTER.get('/showWork/:id', (req, res) => {
    res.sendFile(PATH.join(__dirname, "..") + "/test/showWork.html");
});

ROUTER.get('/submitWork', (req, res) => {
    res.sendFile(PATH.join(__dirname, "..") + "/test/submitWork.html");
});

ROUTER.get('/setProfile', (req, res) => {
    res.sendFile(PATH.join(__dirname, "..") + "/test/avatar_upload.html");
})

ROUTER.get('/comment', (req, res) => {
    res.sendFile(PATH.join(__dirname, "..") + "/test/comment.html");
})

module.exports = ROUTER;