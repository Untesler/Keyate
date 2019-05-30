/* eslint no-unused-vars: 0 */
const EXPRESS        = require("express");
const DBC            = require("../controllers/MongooseConnect");
const MODEL          = require("../models/IllustrationModel");
const COMMENT_MODEL  = require("../models/CommentModel");
const USER_MODEL     = require("../models/UserModel");
const PATH           = require("path");
const FS             = require("fs");
const LOG            = require("../assets/srcs/Log");
const AUTHENICATION  = require("../controllers/Authenication");
const to             = require("await-to-js").default;
const AUTO_INCREMENT = require("../assets/srcs/AutoIncrement");
const IlRank         = require("../assets/srcs/IlRank");

const submitWork = async (req, res) => {
  const auto = require("../assets/srcs/autoCategorize");
  let err;
  const user     = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  const work_uid = await AUTO_INCREMENT.getNextSeq("Illusts"); // generate new uid
  let storePath;
  if (req.body.name === undefined || req.body.name === "") res.sendStatus(406);
  let uploadData = {
    uid            : work_uid,
    name           : req.body.name,
    path           : "upload/illusts/",
    illustrator    : user.uid,
    tag            : [],
    category       : [],
    release_date   : undefined,
    views          : 0,
    populars       : 0,
    deleted        : false,
    comments_box_id: "p" + work_uid.toString()
  };
  let commentBoxData = {
    id     : "p" + work_uid,
    comment: undefined
  };

  if (user) {
    if (req.body.tag !== "" && req.body.tag !== undefined)
      uploadData.tag = req.body.tag.replace(/ /g, "").split(",");
    if (req.body.category !== "" && req.body.category !== undefined)
      uploadData.category = req.body.category.replace(/ /g, "").split(",");
    if (req.body.release_date !== "" && req.body.release_date !== undefined)
      uploadData.release_date = new Date(req.body.release_date);
    if (req.body.description !== "" && req.body.description !== undefined)
      uploadData.description = req.body.description;

    if (req.files !== undefined) {
      // if a file has been uploaded
      if (req.files.work.truncated === false) {
        // if the file size is not larger than 5MB
        if (req.files.work.size > 0) {
          // if the file size is not less than 0
          let   fileData  = req.files.work;
          //extract extension from file name.
          const extension = fileData.name.substring(
            fileData.name.lastIndexOf("."),
            fileData.name.length
          );
          // renaming uploaded file to specific pattern, that identify each file from uid
          const fileName = "i_" + work_uid + extension;
          // set a path for storing the file
          const newpath  = 
            PATH.join(__dirname, "../") +
            "/assets/imgs/upload/illusts/" +
            fileName;
          uploadData.path += fileName;
          storePath        = newpath;

          // move the temp file of uploaded file(move and convert to image file) to the path where the file are stored.
          [err] = await to(fileData.mv(newpath));
          if (err) {
            LOG.write("Error", "Failed to create work image (" + err + ").");
            // if the error occur while moving, then delete the temp file of file.
            FS.unlink(req.files.work.tempFilePath, err => {
              if (err)
                LOG.write("Error", "Failed to delete tmp file (" + err + ").");
            });
            return res.sendStatus(500);
          }
          uploadData.path = "upload/illusts/" + fileName;
          //auto categorize when has no category defined
          if (uploadData.category.length === 0){
            const [category] = await auto.categorize(storePath);
            // if the illustration is tag able and has no tag is defined
            if (category.predictedCategory === "Background" && uploadData.tag.length === 0) {
              let tag = await auto.tag(storePath, 5);
              uploadData.tag = tag.split(",");
            }
            uploadData.category = [category.predictedCategory];
          }
        } else {
          // if the file size if less than 0
          LOG.write(
            "Error",
            "Failed to upload data because size of re files <= 0"
          );
          FS.unlink(req.files.work.tempFilePath, err => {
            if (err)
              LOG.write("Error", "Failed to delete tmp file (" + err + ").");
          });
          return res.sendStatus(406);
        }

        if (DBC.connect()) {
          // if can make a connection to database, then create a new document.
          [err] = await to(MODEL.create(uploadData));
          if (err) {
            LOG.write("Database", "create failed beacause (" + err + ").");
            try {
              // when the error occur that it can't create a new document, So it's necessary to delete the uploaded file.
              await FS.unlink(storePath, e => {
                if (e)
                  LOG.write("Error", `Failed to delete uploaded file (${e}).`);
              });
              // decrease uid of illustration because can't make a new document.
              await AUTO_INCREMENT.decreaseSeq("Illusts");
            } catch (e) {
              LOG.write("Error/Database", `${e}`);
            }
            return res.sendStatus(503);
          } else {
            // if can make a new document of illustration, then let's create a new document for comment box too.
            [err] = await to(COMMENT_MODEL.create(commentBoxData));
            if (err) {
              // when the error occur here that mean we need to delete all of data that involed to this document such as illustration document and uploaded image.
              LOG.write("Database", `create failed because (${err})`);
              try {
                await FS.unlink(storePath, e => {
                  if (e)
                    LOG.write(
                      "Error",
                      `Failed to delete uploaded file (${e}).`
                    );
                });
                await MODEL.deleteOne({ uid: work_uid });
                await AUTO_INCREMENT.decreaseSeq("Illusts");
              } catch (e) {
                LOG.write("Error/Database", `${e}`);
              }
              return res.sendStatus(503);
            } else {
              // if have no any error
              LOG.write(
                "Database",
                `Illust work uid : ${work_uid} and its comment box created.`
              );
            }
          }
          return res.sendStatus(201);
        } else {
          // if can't make a connection with database.
          await AUTO_INCREMENT.decreaseSeq("Illusts");
          return res.sendStatus(503);
        }
      } else {
        // if the uploaded file size has been reached the limit (5 MB).
        LOG.write(
          "Error",
          "Failed to upload data because file size limit has been reached."
        );
        await AUTO_INCREMENT.decreaseSeq("Illusts");
        return res.sendStatus(406);
      }
    } else {
      // when uploaded file can't be found.
      LOG.write("Error", "Failed to submit work because image file not found.");
      await AUTO_INCREMENT.decreaseSeq("Illusts");
      return res.sendStatus(406);
    }
  } else {
    LOG.write(
      "Authenicate",
      "Failed to submit work because token not found or invalid."
    );
    await AUTO_INCREMENT.decreaseSeq("Illusts");
    return res.sendStatus(401);
  }
};

const deleteWork = async (req, res) => {
  let err;
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;

  if (user) {
    if (DBC.connect()) {
      let path;
      // to delete any work things we need to delete any data that involved to it
      [err, path] = await to(
        MODEL.findOne({ uid: req.params.illustID }, "path")
      );
      if (err) {
        LOG.write(
          "Database",
          `Failed to findOne illust uid ${req.params.illustID} because (${err})`
        );
        res.sendStatus(503);
      }
      if (path === "" || path === undefined) {
        return res.sendStatus(200);
      } else {
        // determine the path of the image to use in deletion
        path = PATH.join(__dirname, "../") + `/assets/imgs/${path.path}`;
      }
      // delete the file as a request
      [err] = await to(
        MODEL.deleteOne({ uid: req.params.illustID, illustrator: user.uid })
      );
      if (err) {
        LOG.write("Database", `deleteOne failed because (${err}).`);
        return res.sendStatus(503);
      } else {
        LOG.write(
          "Database",
          `deleteOne work uid : ${req.params.illustID} complete.`
        );
        // delete the illustration of the deleted work.
        await FS.unlink(path, e => {
          if (e) LOG.write("Error", `Failed to delete illust file (${e}).`);
        });
      }
      // delete the comment box of this illustration
      [err] = await to(
        COMMENT_MODEL.deleteOne({ id: `p${req.params.illustID}` })
      );
      DBC.disconnect();
      if (err) {
        LOG.write("Database", `deleteOne failed because (${err}).`);
        return res.sendStatus(503);
      }
      return res.sendStatus(200);
    } else {
      return res.sendStatus(503);
    }
  } else {
    LOG.write(
      "Authenicate",
      "Failed to delete work because token not found or invalid."
    );
    return res.sendStatus(401);
  }
};

const getWork = async (req, res) => {
  /* return the collected data from database as a response */
  let err, workData, illustratorData;
  let result = {};
  if (DBC.connect()) {
    [err, workData] = await to(MODEL.findOne({ uid: req.params.illustID, deleted: false}));
    if (err) {
      LOG.write("Database", `find failed because (${err}).`);
      res.sendStatus(503);
    }
    [err, illustratorData] = await to(USER_MODEL.findOne({ uid: workData.illustrator }));
    if (err) {
      LOG.write("Database", `find failed because (${err}).`);
      res.sendStatus(503);
    }
    DBC.disconnect();
    result.description = workData.description;
    result.tag = workData.tag;
    result.category = workData.category;
    result.release_date = workData.release_date;
    result.views = workData.views;
    result.popularity = workData.popularity;
    result.name = workData.name;
    result.path = workData.path;
    result.uid = workData.uid;
    result.comments_box_id = workData.comments_box_id;
    result.illustratorId =workData.illustrator;
    result.illustratorPenname = illustratorData.penname;
    result.illustratorAvatar = illustratorData.avatar;
    result.illustratorRank = illustratorData.rank;
    res.json(result);
  } else {
    return res.sendStatus(503);
  }
};

const addView = async (req, res) => {
  /* increase the value of view filed with 1 */
  let illustData, illustratorData;
  let user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  let viewer = "";
  let popularity;
  const IlRankObj = new IlRank();

  if (DBC.connect()) {
    try {
      illustData = await MODEL.findOne({ uid: req.params.illustID }, "views popularity illustrator release_date");
      if(user) viewer = user.uid === illustData.illustrator ? "owner" : "user";
      else viewer = "guest";
      popularity = IlRankObj.popularity(viewer, "view", illustData.release_date.getTime());
      await MODEL.updateOne(
        { uid: req.params.illustID },
        { views: illustData.views + 1,
          popularity: illustData.popularity + popularity
        }
      );
      illustratorData = await USER_MODEL.findOne({ uid: illustData.illustrator }, "rank");
      await USER_MODEL.updateOne(
        { uid: illustData.illustrator },
        { rank: illustratorData.rank + popularity }
      );
      DBC.disconnect()
    } catch (e) {
      LOG.write("Database", `findOne/updateOne failed because (${e}).`);
      return res.sendStatus(503);
    }
    return res.status(201);
  } else {
    LOG.write(
      "Authenicate",
      "Failed to delete work because token not found or invalid."
    );
    return res.sendStatus(401);
  }
};

const addPopular = async (req, res) => {
  /* increase the value of popular filed with 1 and push the work_uid to favorite list of user */
  let illustData, illustratorData;
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  let viewer = "";
  let popularity;
  const IlRankObj = new IlRank();
  if (user) {
    if (DBC.connect()) {
      try {
        illustData = await MODEL.findOne({ uid: req.params.illustID }, "popularity illustrator release_date");
        if(illustData === null) return res.sendStatus(404);
        if(user) viewer = user.uid === illustData.illustrator ? "owner" : "user";
        else viewer = "guest";
        popularity = IlRankObj.popularity(viewer, "favorite", illustData.release_date.getTime());
        await MODEL.updateOne(
          { uid: req.params.illustID },
          { popularity: illustData.popularity + popularity }
        );
        illustratorData = await USER_MODEL.findOne({ uid: illustData.illustrator }, "rank");
        await USER_MODEL.updateOne(
          { uid: illustData.illustrator },
          { rank: illustratorData.rank + popularity }
        );
        await USER_MODEL.updateOne(
          { uid: user.uid },
          { $push: { favorites: req.params.illustID } }
        );
      } catch (e) {
        LOG.write("Database", `findOne/updateOne failed because (${e}).`);
        DBC.disconnect();
        return res.sendStatus(503);
      }
      DBC.disconnect();
      LOG.write("Database", `User[${user.uid}] - add favorite complete.`);
      return res.sendStatus(201);
    }
  } else {
    LOG.write(
      "Authenicate",
      "Failed to delete work because token not found or invalid."
    );
    return res.sendStatus(401);
  }
};

const listWork = async (req, res) => {
  /* list all work from user */
  let err, workList;
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  if (user) {
    if (DBC.connect()) {
      [err, workList] = await to(
        MODEL.find({ illustrator: user.uid }, "uid") // find where illustrator, and only selecting uid field
      );
      DBC.disconnect();
      if (err) {
        LOG.write("Database", `find failed because (${err}).`);
        res.sendStatus(503);
      } else {
        if (workList !== null) {
          // Convert json to array
          workList = workList.map(doc => {
            return doc.uid;
          });
          return res.json(workList);
        } else {
          return res.json([]);
        }
      }
    } else {
      return res.sendStatus(503);
    }
  } else {
    LOG.write(
      "Authenicate",
      "Failed to delete work because token not found or invalid."
    );
    return res.sendStatus(401);
  }
};

const listAllWork = async (req, res) => {
  /* list all work from all user, no need to sign-in*/
  let err, workList;
  if (DBC.connect()) {
    [err, workList] = await to(MODEL.find({}, "uid"));
    DBC.disconnect();
    if (err) {
      LOG.write("Database", `find failed beacause (${err}).`);
      res.sendStatus(503);
    } else {
      if (workList !== null) {
        workList = workList.map(doc => {
          return doc.uid;
        });
        return res.json(workList);
      } else {
        return res.json([]);
      }
    }
  } else {
    return res.sendStatus(503);
  }
};

module.exports = {
  submitWork,
  deleteWork,
  getWork,
  addView,
  addPopular,
  listWork,
  listAllWork
};
