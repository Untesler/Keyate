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

const submitWork = async (req, res) => {
  let err;
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  const work_uid = await AUTO_INCREMENT.getNextSeq("Illustrations");
  if (req.body.name === undefined || req.body.name === "") res.sendStatus(406);
  let uploadData = {
    uid            : work_uid,
    name           : req.body.name,
    path           : "illusts/",
    illustrator    : user.uid,
    tag            : [],
    category       : [],
    release_date   : new Date(Date.now),
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
      if (req.files.work.truncated === false) {
        if (req.files.work.size > 0) {
          let fileData = req.files.work;
          const extension = fileData.name.substring(
            fileData.name.lastIndexOf("."),
            fileData.name.length
          );
          const fileName = "i_" + user.uid + extension;
          const newpath =
            PATH.join(__dirname, "../") +
            "/assets/imgs/upload/illusts/" +
            fileName;
          uploadData.path += fileName;

          [err] = await to(fileData.mv(newpath));
          if (err) {
            LOG.write("Error", "Failed to create work image (" + err + ").");
            FS.unlink(req.files.work.tempFilePath, err => {
              if (err)
                LOG.write("Error", "Failed to delete tmp file (" + err + ").");
            });
            return res.sendStatus(500);
          }
          uploadData.path = "illusts/" + fileName;
        } else {
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
          [err] = await to(MODEL.create(uploadData));
          if (err) {
            LOG.write("Database", "create failed beacause (" + err + ").");
            await AUTO_INCREMENT.decreaseSeq("Illusts");
            return res.sendStatus(503);
          } else {
            [err] = await to(COMMENT_MODEL.create(commentBoxData));
            if (err) {
              LOG.write("Database", `create failed because (${err})`);
              await MODEL.deleteOne({ uid: work_uid });
              await AUTO_INCREMENT.decreaseSeq("Illusts");
              return res.sendStatus(503);
            } else {
              LOG.write(
                "Database",
                `Illust work uid : ${work_uid} and its comment box created.`
              );
            }
          }
          return res.sendStatus(201);
        } else {
          await AUTO_INCREMENT.decreaseSeq("Illusts");
          return res.sendStatus(503);
        }
      } else {
        LOG.write(
          "Error",
          "Failed to upload data because file size limit has been reached."
        );
        await AUTO_INCREMENT.decreaseSeq("Illusts");
        return res.sendStatus(406);
      }
    } else {
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
    if (req.body.uid === "" || req.body.uid === undefined)
      return res.sendStatus(406);
    else {
      if (DBC.connect()) {
        [err] = await to(
          MODEL.deleteOne({ uid: req.body.uid, illustrator: user.uid })
        );
        DBC.disconnect();
        if (err) {
          LOG.write("Database", `deleteOne failed because (${err}).`);
          return res.sendStatus(503);
        } else {
          LOG.write(
            "Database",
            `deleteOne work uid : ${req.body.uid} complete.`
          );
          return res.sendStatus(200);
        }
      } else {
        return res.sendStatus(503);
      }
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
  let err, workData;
  if (req.body.uid === "" || req.body.uid === undefined)
    return res.sendStatus(406);
  else {
    if (DBC.connect()) {
      [err, workData] = await to(MODEL.find({ uid: req.body.uid }));
      DBC.disconnect();
      if (err) {
        LOG.write("Database", `find failed because (${err}).`);
        res.sendStatus(503);
      } else {
        res.json(workData);
      }
    } else {
      return res.sendStatus(503);
    }
  }
};

const addView = async (req, res) => {
  let total;
  if (req.body.uid === "" || req.body.uid === undefined)
    return res.sendStatus(406);
  else {
    if (DBC.connect()) {
      try {
        total = await MODEL.findOne({ uid: req.body.uid }, "views");
        await MODEL.updateOne(
          { uid: req.body.uid },
          { views: total.views + 1 }
        );
        return res.status(200);
      } catch (e) {
        LOG.write("Database", `findOne/updateOne failed because (${e}).`);
        DBC.disconnect();
        return res.sendStatus(503);
      }
    } else {
      LOG.write(
        "Authenicate",
        "Failed to delete work because token not found or invalid."
      );
      return res.sendStatus(401);
    }
  }
};

const addPopular = async (req, res) => {
  let total;
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  if (req.body.uid === "" || req.body.uid === undefined)
    return res.sendStatus(406);
  else {
    if (user) {
      if (DBC.connect()) {
        try {
          total = await MODEL.findOne({ uid: req.body.uid }, "populars");
          await MODEL.updateOne(
            { uid: req.body.uid },
            { populars: total.populars + 1 }
          );
          await USER_MODEL.updateOne(
            { uid: user.uid },
            { $push: { favorites: req.body.uid } }
          );
        } catch (e) {
          LOG.write("Database", `findOne/updateOne failed because (${e}).`);
          DBC.disconnect();
          return res.sendStatus(503);
        }
      }
    } else {
      LOG.write(
        "Authenicate",
        "Failed to delete work because token not found or invalid."
      );
      return res.sendStatus(401);
    }
  }
};

const listWork = async (req, res) => {
  let err, workList;
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  if (user) {
    if (DBC.connect()) {
      [err, workList] = await to(
        MODEL.find({ illustrator: user.uid }, "uid")
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

module.exports = {
  submitWork,
  deleteWork,
  getWork,
  addView,
  addPopular,
  listWork
};
