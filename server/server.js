const EXPRESS     = require("express");
const BODYPARSER  = require("body-parser");
const FILEUPLOAD  = require("express-fileupload");
const CORS        = require("cors");
const BEARERTOKEN = require("express-bearer-token");
const PATH        = require("path");
const MKDIRP      = require("mkdirp")

const SERVER = EXPRESS();
const PORT   = process.env.PORT || 3000;
//Google application credential use for access to Vision API for use auto categorize and taging feature.
//Use in IllustController on lines 86, if you don't have a  google application credential you can delete those lines
process.env.GOOGLE_APPLICATION_CREDENTIALS = PATH.join(__dirname, "config/google_app_credential.json");

// Routes import
const USERS    = require("./routes/users");
const ILLUSTS  = require('./routes/illust');
const SEARCH  = require('./routes/search');
const COMMENTS = require("./routes/comments");
const TEST     = require("./routes/test");

// Middleware
MKDIRP(PATH.join(__dirname, "assets/tmp"), (err) => {
  if(err) console.log(`Error occur when creating necessary directories(Error: ${err})`);
  else console.log(`${PATH.join(__dirname, "assets/tmp")} directory created.`);
});
MKDIRP(PATH.join(__dirname, "assets/logs"), (err) => {
  if(err) console.log(`Error occur when creating necessary directories(Error: ${err})`);
  else console.log(`${PATH.join(__dirname, "assets/logs")} directory created.`);
});
MKDIRP(PATH.join(__dirname, "assets/imgs/upload/illusts"), (err) => {
  if(err) console.log(`Error occur when creating necessary directories(Error: ${err})`);
  else console.log(`${PATH.join(__dirname, "assets/imgs/upload/illusts")} directory created.`);
});
SERVER.use(BODYPARSER.urlencoded({ limit: '10mb', extended: true }));
SERVER.use(BODYPARSER.json({ limit: '10mb', extended: true }));
SERVER.use(BEARERTOKEN());
SERVER.use(CORS());
SERVER.use(
  FILEUPLOAD({
    limits: { fileSize: 5 * 1024 * 1024 /* 5MB */ },
    abortOnLimit: true,
    limitHandler: (res) => {
      return res.json({ status: "File size limit has been reached" });
    },
    useTempFiles: true,
    tempFileDir: __dirname + "/assets/tmp/"
  })
);

// Routes
SERVER.use("/users", USERS);
SERVER.use("/illusts", ILLUSTS);
SERVER.use("/comments", COMMENTS);
SERVER.use("/search", SEARCH);
SERVER.use(
  "/avatars",
  EXPRESS.static(__dirname + "/assets/imgs/upload/avatars")
);
SERVER.use(
  "/upload/illusts",
  EXPRESS.static(__dirname + "/assets/imgs/upload/illusts")
);

// Redundant path, used for unit test only
//SERVER.use("/test", TEST);

//if have no any route to handle this req or req.url are index or default
SERVER.get("*", (req, res) => {
  res.setHeader("Content-Type", "text/html");
  res.sendStatus(403);
});

SERVER.listen(PORT, () => console.log(`Server started on port : ${PORT}`));
