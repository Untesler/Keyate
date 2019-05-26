const EXPRESS = require("express");
const ROUTER = EXPRESS.Router();
const PATH = require("path");
const auto = require("../assets/srcs/autoCategorize");

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

ROUTER.get("/label/:name", async (req, res) => {
  const imgPath = PATH.join(__dirname, "../") +
        "/assets/imgs/upload/illusts/" +
        req.params.name;
  const labels = await auto.labelImg(`${imgPath}`);

  res.json(labels)
})

ROUTER.get("/tag/:id", async (req, res) => {
  const imgPath = PATH.join(__dirname, "../") +
        "/assets/imgs/upload/illusts/" +
        req.params.id;
  const tag = await auto.tag(`${imgPath}`, 4);
  return res.send(tag);
})

ROUTER.get('/classify/:id', async (req, res) => {
  const imgPath = PATH.join(__dirname, "../") +
        "/assets/imgs/upload/illusts/" +
        req.params.id;
  const [category]= await auto.categorize(`${imgPath}`);
  return res.send(category);
})
module.exports = ROUTER;