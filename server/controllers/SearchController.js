/* eslint no-unused-vars: 0 */
const DBC          = require("../controllers/MongooseConnect");
const ILLUST_MODEL = require("../models/IllustrationModel");
const USER_MODEL   = require("../models/UserModel");
const LOG          = require("../assets/srcs/Log");
const to           = require("await-to-js").default;

const fullSearch = async (req, res) => {
  const keyword    = req.params.keyword,
        tags       = req.params.tags === "*" ? "*" : req.params.tags.split(","),
        categories = req.params.categories === "*" ? "*" : req.params.categories.split(","),
        orderBy    = parseInt(req.params.orderBy),                                                           // 0 == release_date, 1 == popularity
        searchType = parseInt(req.params.searchType);                                                        // 0 == illustration name, 1 == penname
  let err,
    illusts,
    illustrator,
    j = 0;
  let searchResult = {},
    resultFilter = {};
  if (orderBy > 1 || orderBy < 0 || searchType > 1 || searchType < 0)
    return res.sendStatus(406);
  if (DBC.connect()) {
    if (searchType === 0) {
      // search from illustration name
      if (orderBy === 0) {
        //orderBy release_date
        if (keyword === "*")
          [err, illusts] = await to(
            ILLUST_MODEL.find({ deleted: false }).sort({ release_date: -1 })
          );
        else
          [err, illusts] = await to(
            ILLUST_MODEL.find({
              name: { $regex: keyword, $options: "i" },
              deleted: false
            }).sort({ release_date: -1 })
          );
      } else {
        //orderBy popularity
        if (keyword === "*")
          [err, illusts] = await to(
            ILLUST_MODEL.find({ deleted: false }).sort({ popularity: -1 })
          );
        else
          [err, illusts] = await to(
            ILLUST_MODEL.find({
              name: { $regex: keyword, $options: "i" },
              deleted: false
            }).sort({ popularity: -1 })
          );
      }
      if (illusts === null) return res.json({});
      if (err) return res.sendStatus(503);
      for (let i in illusts) {
        [err, illustrator] = await to(
          USER_MODEL.findOne({ uid: illusts[i].illustrator }, "penname")
        );
        if (err) return res.sendStatus(503);
        searchResult[i]                    = {};
        searchResult[i].uid                = illusts[i].uid;
        searchResult[i].name               = illusts[i].name;
        searchResult[i].path               = illusts[i].path;
        searchResult[i].illustratorId      = illusts[i].illustrator;
        searchResult[i].illustratorPenname = illustrator.penname;
        searchResult[i].tag                = illusts[i].tag;
        searchResult[i].category           = illusts[i].category;
        searchResult[i].popularity         = illusts[i].popularity;
        searchResult[i].views              = illusts[i].views;
        searchResult[i].release_date       = illusts[i].release_date;
      }
      DBC.disconnect();
    } else {
      // search from penname
      if (orderBy === 0) {
        //orderBy release_date
        if (keyword === "*")
          [err, illusts] = await to(
            ILLUST_MODEL.find({ deleted: false }).sort({ release_date: -1 })
          );
        else {
          [err, illustrator] = await to(
            USER_MODEL.findOne({ penname: keyword }, "uid")
          );
          if (err) return res.sendStatus(503);
          if (illustrator === null) return res.sendStatus(204);
          [err, illusts] = await to(
            ILLUST_MODEL.find({
              illustrator: illustrator.uid,
              deleted: false
            }).sort({ release_date: -1 })
          );
        }
      } else {
        //orderBy popularity
        if (keyword === "*")
          [err, illusts] = await to(
            ILLUST_MODEL.find({ deleted: false }).sort({ popularity: -1 })
          );
        else {
          [err, illustrator] = await to(
            USER_MODEL.findOne({ penname: keyword }, "uid")
          );
          if (err) return res.sendStatus(503);
          if (illustrator === null) return res.json({});
          [err, illusts] = await to(
            ILLUST_MODEL.find({
              illustrator: illustrator.uid,
              deleted: false
            }).sort({ popularity: -1 })
          );
        }
      }
      if (illusts === null) return res.json({});
      if (err) return res.sendStatus(503);
      for (let i in illusts) {
        searchResult[i] = {};
        if (keyword === "*") {
          [err, illustrator] = await to(
            USER_MODEL.findOne({ uid: illusts[i].illustrator }, "penname")
          );
          if (err) return res.sendStatus(503);
          searchResult[i].illustratorId      = illusts[i].illustrator;
          searchResult[i].illustratorPenname = illustrator.penname;
        } else {
          searchResult[i].illustratorId      = illustrator.uid;
          searchResult[i].illustratorPenname = keyword;
        }
        searchResult[i].uid          = illusts[i].uid;
        searchResult[i].name         = illusts[i].name;
        searchResult[i].path         = illusts[i].path;
        searchResult[i].tag          = illusts[i].tag;
        searchResult[i].category     = illusts[i].category;
        searchResult[i].popularity   = illusts[i].popularity;
        searchResult[i].views        = illusts[i].views;
        searchResult[i].release_date = illusts[i].release_date;
      }
    }
    DBC.disconnect();
    if (categories !== "*") {
      //filter according to given categorires list
      for (let i in searchResult) {
        if (
          searchResult[i].category.some(e => {
            return categories.includes(e);
          })
        ) {
          resultFilter[j] = searchResult[i];
          j++;
        }
      }
      return res.json(resultFilter);
    } else if (tags !== "*") {
      //filter according to given tags list
      for (let i in searchResult) {
        if (
          searchResult[i].tag.some(e => {
            return tags.includes(e);
          })
        ) {
          resultFilter[j] = searchResult[i];
          j++;
        }
      }
      return res.json(resultFilter);
    } else {
      //have no categories or tags list filter
      return res.json(searchResult);
    }
  } else {
    return res.sendStatus(503);
  }
};

const quickSearch = async (req, res) => {
  const keyword = req.params.keyword;
  let err, results, illustratorName;
  let searchResult = {};
  if (DBC.connect()) {
    /*
     *   Multiple keyword search
     *   [err, results] = await to( ILLUST_MODEL.find({description: {$regex: desc, $options: 'i'}, name: {$regex: keyword, $options: 'i'}, deleted: false}).sort({ popularity: -1 }));
     **/
    [err, results] = await to(
      ILLUST_MODEL.find({
        name: { $regex: keyword, $options: "i" },
        deleted: false
      }).sort({ popularity: -1 })
    );
    if (err) return res.sendStatus(503);
    for (let i in results) {
      [err, illustratorName] = await to(
        USER_MODEL.findOne({ uid: results[i].illustrator }, "penname")
      );
      if (err) return res.json(searchResult);
      searchResult[i]                    = {};
      searchResult[i].uid                = results[i].uid;
      searchResult[i].name               = results[i].name;
      searchResult[i].path               = results[i].path;
      searchResult[i].illustratorId      = results[i].illustrator;
      searchResult[i].illustratorPenname = illustratorName.penname;
      searchResult[i].description        = results[i].description;
      searchResult[i].tag                = results[i].tag;
      searchResult[i].category           = results[i].category;
      searchResult[i].release_date       = results[i].release_date;
      searchResult[i].views              = results[i].views;
      searchResult[i].popularity         = results[i].popularity;
      searchResult[i].comments_box_id    = results[i].comments_box_id;
    }
    DBC.disconnect();
    return res.json(searchResult);
  } else {
    return res.sendStatus(503);
  }
};

const rankSearch = async (req, res) => {
  const nRow = parseInt(req.params.nRow);
  let results, err;
  if (DBC.connect()) {
    if (nRow > 0) {
      [err, results] = await to(
        USER_MODEL.find({}, "uid penname avatar rank followers")
          .sort({ rank: -1 })
          .limit(nRow)
      );
    } else {
      [err, results] = await to(
        USER_MODEL.find({}, "uid penname avatar rank followers").sort({
          rank: -1
        })
      );
    }
    DBC.disconnect();
    if (err) return res.sendStatus(503);
    return res.json(results);
  } else return res.sendStatus(503);
};

const popularSearch = async (req, res) => {
  const nRow = parseInt(req.params.nRow);
  let err, results, illustratorName;
  let searchResult = {};
  if (DBC.connect()) {
    if (nRow > 0)
      [err, results] = await to(
        ILLUST_MODEL.find({ deleted: false })
          .sort({ popularity: -1 })
          .limit(nRow)
      );
    else
      [err, results] = await to(
        ILLUST_MODEL.find({ deleted: false }).sort({ popularity: -1 })
      );
    if (err) return res.sendStatus(503);
    for (let i in results) {
      [err, illustratorName] = await to(
        USER_MODEL.findOne({ uid: results[i].illustrator }, "penname")
      );
      if (err) return res.json(searchResult);
      searchResult[i]                    = {};
      searchResult[i].uid                = results[i].uid;
      searchResult[i].name               = results[i].name;
      searchResult[i].path               = results[i].path;
      searchResult[i].illustratorId      = results[i].illustrator;
      searchResult[i].illustratorPenname = illustratorName.penname;
      searchResult[i].description        = results[i].description;
      searchResult[i].tag                = results[i].tag;
      searchResult[i].category           = results[i].category;
      searchResult[i].release_date       = results[i].release_date;
      searchResult[i].views              = results[i].views;
      searchResult[i].popularity         = results[i].popularity;
      searchResult[i].comments_box_id    = results[i].comments_box_id;
    }
    DBC.disconnect();
    return res.json(searchResult);
  } else {
    return res.sendStatus(503);
  }
};

const userIllusts = async (req, res) => {
  const uid = parseInt(req.params.uid);
  let err, results, illustratorName;
  let searchResult = {};

  if (DBC.connect()) {
    [err, results] = await to(
      ILLUST_MODEL.find({ illustrator: uid, deleted: false })
    );
    if (err) return res.sendStatus(503);
    for (let i in results) {
      [err, illustratorName] = await to(
        USER_MODEL.findOne({ uid: results[i].illustrator }, "penname")
      );
      if (err) return res.json(searchResult);
      searchResult[i]                    = {};
      searchResult[i].uid                = results[i].uid;
      searchResult[i].name               = results[i].name;
      searchResult[i].path               = results[i].path;
      searchResult[i].illustratorId      = results[i].illustrator;
      searchResult[i].illustratorPenname = illustratorName.penname;
      searchResult[i].description        = results[i].description;
      searchResult[i].tag                = results[i].tag;
      searchResult[i].category           = results[i].category;
      searchResult[i].release_date       = results[i].release_date;
      searchResult[i].views              = results[i].views;
      searchResult[i].popularity         = results[i].popularity;
      searchResult[i].comments_box_id    = results[i].comments_box_id;
    }
    DBC.disconnect();
    return res.json(searchResult);
  } else {
    return res.sendStatus(503);
  }
};

const userFollowers = async (req, res) => {
  const uid = parseInt(req.params.uid);
  let err,
    userData,
    followerData,
    i = 0;
  let searchResult = {};

  if (DBC.connect()) {
    [err, userData] = await to(USER_MODEL.findOne({ uid: uid }, "followers"));
    if (err) return res.sendStatus(503);
    if (userData === null || userData.followers.length === 0)
      return res.json({});
    for (let follower of userData.followers) {
      [err, followerData] = await to(
        USER_MODEL.findOne(
          { uid: follower },
          "uid penname avatar followers following rank description gender birthdate"
        )
      );
      if (err) return res.sendStatus(503);
      if (followerData !== null) {
        searchResult[i] = followerData;
        i++;
      }
    }
    DBC.disconnect();
    return res.json(searchResult);
  } else {
    return res.sendStatus(503);
  }
};

const userFollowing = async (req, res) => {
  const uid = parseInt(req.params.uid);
  let err,
    userData,
    followingData,
    i = 0;
  let searchResult = {};

  if (DBC.connect()) {
    [err, userData] = await to(USER_MODEL.findOne({ uid: uid }, "following"));
    if (err) return res.sendStatus(503);
    if (userData === null || userData.following.length === 0)
      return res.json({});
    for (let following of userData.following) {
      [err, followingData] = await to(
        USER_MODEL.findOne(
          { uid: following },
          "uid penname avatar followers following rank description gender birthdate"
        )
      );
      if (err) return res.sendStatus(503);
      if (followingData !== null) {
        searchResult[i] = followingData;
        i++;
      }
    }
    DBC.disconnect();
    return res.json(searchResult);
  } else {
    return res.sendStatus(503);
  }
};

const listBookmarks = async (req, res) => {
  const AUTHENICATION = require("../controllers/Authenication");
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  let err,
    result,
    illusts,
    illustrator,
    i = 0;
  let bookmarks = {};

  if (user) {
    if (DBC.connect()) {
      [err, result] = await to(
        USER_MODEL.findOne({ uid: user.uid }, "favorites")
      );
      if (err) return res.sendStatus(503);
      for (let wID of result.favorites) {
        [err, illusts] = await to(
          ILLUST_MODEL.findOne(
            { uid: wID, deleted: false },
            "uid name illustrator"
          )
        );
        if (err) return res.sendStatus(503);
        [err, illustrator] = await to(
          USER_MODEL.findOne({ uid: illusts.illustrator }, "penname")
        );
        if (err) return res.sendStatus(503);
        bookmarks[i]                    = {};
        bookmarks[i].uid                = illusts.uid;
        bookmarks[i].name               = illusts.name;
        bookmarks[i].illustratorId      = illusts.illustrator;
        bookmarks[i].illustratorPenname = illustrator.penname;
        i++;
      }
      DBC.disconnect();
      return res.json(bookmarks);
    } else {
      return res.sendStatus(503);
    }
  } else {
    return res.sendStatus(401);
  }
};

module.exports = {
  fullSearch,
  quickSearch,
  rankSearch,
  popularSearch,
  userIllusts,
  userFollowers,
  userFollowing,
  listBookmarks
};
