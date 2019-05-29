const EXPRESS     = require("express");
const BODYPARSER  = require("body-parser");
const FILEUPLOAD  = require("express-fileupload");
const CORS        = require("cors");
const BEARERTOKEN = require("express-bearer-token");
const PATH        = require("path");

const SERVER = EXPRESS();
const PORT   = process.env.PORT || 3000;
//Google application credential use for access to Vision API for use auto categorize and taging feature.
process.env.GOOGLE_APPLICATION_CREDENTIALS = PATH.join(__dirname, "config/google_app_credential.json");

// Routes import
const USERS    = require("./routes/users");
const ILLUSTS  = require('./routes/illust');
const SEARCH  = require('./routes/search');
const COMMENTS = require("./routes/comments");
const TEST     = require("./routes/test");

// Middleware
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
  res.sendStatus(404);
});

SERVER.listen(PORT, () => console.log(`Server started on port : ${PORT}`));
