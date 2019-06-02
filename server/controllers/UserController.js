/* eslint no-unused-vars: 0 */
const EXPRESS        = require("express");
const DBC            = require("../controllers/MongooseConnect");
const MODEL          = require("../models/UserModel");
const COUNTER_MODEL  = require("../models/CounterModel");
const JWT            = require("jsonwebtoken");
const BCRYPT         = require("bcrypt");
const PATH           = require("path");
const FS             = require("fs");
const LOG            = require("../assets/srcs/Log");
const AUTHENICATION  = require("../controllers/Authenication");
const to             = require("await-to-js").default;
const AUTO_INCREMENT = require('../assets/srcs/AutoIncrement');
const PRIVATE_KEY    = FS.readFileSync(
  PATH.resolve(__dirname, "../config/private.key")
);

const getData = async (req, res) => {
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  if (user && user.uid === 0) {
    res.send(await MODEL.find({}));
  } else {
    res.send("Can't make a connection.");
  }
  DBC.disconnect();
};

const register = async (req, res) => {
  let err;

  if (
    req.body.email    === undefined ||
    req.body.penname  === undefined ||
    req.body.password === undefined ||
    req.body.email    === "" ||
    req.body.penname  === "" ||
    req.body.password === ""
  )
    return res.sendStatus(406);

  if (DBC.connect()) {
    req.body.password = BCRYPT.hashSync(req.body.password, 10);
    const data = {
      uid        : await AUTO_INCREMENT.getNextSeq('Users'),
      penname    : req.body.penname,
      email      : req.body.email,
      password   : req.body.password,
      gender     : undefined,
      birthdate  : undefined,
      description: undefined,
      follower   : undefined,
      avatar     : undefined
    };

    [err] = await to(MODEL.create(data));
    if (err) {
      let status = "";
      await AUTO_INCREMENT.decreaseSeq('Users');
      if (err.message.search("keyate.Users index: email") !== -1)
        status = "Duplicate email";
      else if (err.message.search("keyate.Users index: penname") !== -1)
        status = "Duplicate penname";
      res.json({ status: status });
    } else {
      res.json({ status: "Registered" });
    }
    DBC.disconnect();
  } else {
    await AUTO_INCREMENT.decreaseSeq('Users');
    res.send("Can't make a connection.");
  }
};

const signIn = async (req, res) => {
  let err, user;

  if (
    req.body.email    === undefined ||
    req.body.password === undefined ||
    req.body.email    === "" ||
    req.body.password === ""
  )
    return res.sendStatus(406);

  if (DBC.connect()) {
    [err, user] = await to(MODEL.findOne({ email: req.body.email }));
    if (err) {
      res.sendStatus(503);
      LOG.write("Database", "findOne failed because (" + err + ")");
    }
    if (user) {
      if (BCRYPT.compareSync(req.body.password, user.password)) {
        const payload = {
          uid: user.uid,
          email: user.email
        };
        const token = JWT.sign(payload, PRIVATE_KEY, {
          expiresIn: "24h"
        });
        res.json({token: token, status: "accept"});
      } else res.json({ status: "Password mismatch." });
    } else {
      res.json({ status: "User does not exist" });
    }
    DBC.disconnect();
  } else {
    res.sendStatus(503);
  }
};

const setProfile = async (req, res) => {
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  let updateData = new Object();
  let err, old_avatar;

  if (user) {
    if (req.body.penname !== "" && req.body.penname !== undefined)
      updateData.penname = req.body.penname;
    if (req.body.password !== "" && req.body.password !== undefined)
      updateData.password = BCRYPT.hashSync(req.body.password, 10);
    if (req.body.gender !== "" && req.body.gender !== undefined)
      updateData.gender = req.body.gender;
    if (req.body.birthdate !== "" && req.body.birthdate !== undefined)
      updateData.birthdate = new Date(req.body.birthdate);
    if (req.body.description !== "" && req.body.description !== undefined)
      updateData.description = req.body.description;

    if (req.files !== undefined && req.files !== null) {
      if (req.files.avatar.truncated === false) {
        if (req.files.avatar.size > 0) {
          let fileData = req.files.avatar;
          const extension = fileData.name.substring(
            fileData.name.lastIndexOf("."),
            fileData.name.length
          );
          const fileName = "ava_" + user.uid + extension;
          const newpath =
            PATH.join(__dirname, "../") +
            "/assets/imgs/upload/avatars/" +
            fileName;
          [err, old_avatar] = await to(
            MODEL.findOne({ uid: user.uid }, "avatar")
          );
          if (err) {
            LOG.write("Database", "findOne failed because (" + err + ").");
            return res.sendStatus(503);
          }
          if (old_avatar === null) return res.sendStatus(404);
          if (old_avatar.avatar != "avatars/default.png") {
            FS.unlink(
              PATH.resolve(
                __dirname,
                "../assets/imgs/upload/" + old_avatar.avatar
              ),
              err => {
                if (err) {
                  LOG.write(
                    "Error",
                    "Failed to delete old avatar (" + err + ")."
                  );
                  return res.sendStatus(500);
                }
              }
            );
          }

          [err] = await to(fileData.mv(newpath));
          if (err) {
            LOG.write("Error", "Failed to create avatar image (" + err + ").");
            FS.unlink(req.files.avatar.tempFilePath, err => {
              if (err)
                LOG.write("Error", "Failed to delete tmp file (" + err + ").");
            });
            return res.sendStatus(500);
          }
          updateData.avatar = "avatars/" + fileName;
        } else {
          LOG.write(
            "Error",
            "Failed to upload avatar becaause size of req file <= 0"
          );
          FS.unlink(req.files.avatar.tempFilePath, err => {
            if (err)
              LOG.write("Error", "Failed to delete tmp file (" + err + ").");
          });
        }
      } else {
        LOG.write(
          "Error",
          "Failed to upload data because file size limit has been reached."
        );
        return res.sendStatus(406);
      }
    }

    [err] = await to(MODEL.updateOne({ uid: user.uid }, updateData));
    if (err) {
      LOG.write("Database", "updateOne failed beacause (" + err + ").");
      return res.sendStatus(503);
    } else {
      LOG.write("Database", "Uid[" + user.uid + "] Update profile success.");
    }
    return res.sendStatus(201);
  } else {
    if (req.files !== undefined) {
      FS.unlink(req.files.avatar.tempFilePath, err => {
        if (err) LOG.write("Error", "Failed to delete tmp file (" + err + ").");
      });
    }
    return res.sendStatus(401);
  }
};

const getFavorites = async (req, res) => {
  let err, favs;
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;

  if (user) {
    [err, favs] = await to(MODEL.findOne({ uid: user.uid }, "favorites"));
    if (err) {
      LOG.write("Database", "findOne failed because(" + err + ")");
      return res.sendStatus(503);
    } else {
      DBC.disconnect();
      return res.json({
        Total    : favs.favorites.length,
        favorites: favs.favorites
      });
    }
  } else {
    LOG.write(
      "Authenicate",
      "Failed to get favorites because token not found."
    );
    return res.sendStatus(401);
  }
};

const getFollowers = async (req, res) => {
  let err, fol;
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;

  if (user) {
    [err, fol] = await to(MODEL.findOne({ uid: user.uid }, "followers"));
    if (err) {
      LOG.write("Database", "findOne failed because(" + err + ")");
      return res.sendStatus(503);
    } else {
      DBC.disconnect();
      return res.json({
        Total    : fol.followers.length,
        followers: fol.followers
      });
    }
  } else {
    LOG.write(
      "Authenicate",
      "Failed to get followers because token not found."
    );
    return res.sendStatus(401);
  }
};

const verify = async (req, res) =>{
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  let err, userData;
  if(user){
    [err, userData] = await to(MODEL.findOne({ uid: user.uid }, "uid email penname gender birthdate description avatar"));
    res.json(userData);
  }
  else
    res.json({status: false});
}

const existance = async (req, res) =>{
  const uid = req.params.uid;
  let err, userData;
  if(DBC.connect()){
    [err, userData] = await to(MODEL.findOne({ uid: uid }, "penname gender birthdate description avatar"));
    res.json(userData);
  }
  else
    res.json({status: false});
}

module.exports = {
  getData,
  register,
  signIn,
  setProfile,
  getFavorites,
  getFollowers,
  verify,
  existance
};
