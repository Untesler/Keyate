/* eslint no-unused-vars: 0 */
const DBC           = require("../controllers/MongooseConnect");
const MODEL         = require("../models/CommentModel");
const USER_MODEL    = require("../models/UserModel");
const LOG           = require("../assets/srcs/Log");
const AUTHENICATION = require("../controllers/Authenication");
const to            = require("await-to-js").default;
const MONGOOSE      = require("mongoose");

const getAllComments = async (req, res) => {
  const id = req.params.id;
  let err, comments, results = [], user, i = 0;
  /* find every comment in comments that have not been deleted yet,
     and list only commentator uid, helpful point, comment and comment date 
     Read more: https://techbrij.com/mongodb-query-select-filter-child-nested-array */
  if (DBC.connect()) {
    [err, comments] = await to(
      MODEL.aggregate([
        { $unwind: "$comments" },
        { $match: { id: id, "comments.deleted": false } },
        {
          $project: {
            _id        : "$comments._id",
            commentator: "$comments.commentator",
            helpful    : "$comments.helpful",
            comment    : "$comments.comment",
            date       : "$comments.date"
          }
        }
      ])
    );

    for(let comment of comments){
      [err, user] = await to(USER_MODEL.findOne({ uid: comment.commentator }, "penname avatar"));
      if(err || user === null) continue;
      let result                    = {};
          result._id                = comment._id;
          result.commentator        = comment.commentator;
          result.helpful            = comment.helpful;
          result.comment            = comment.comment;
          result.date               = comment.date;
          result.commentatorPenname = user.penname;
          result.commentatorAvatar  = user.avatar;
      results.push(result);
    }
    // [err, comments] = await to(MODEL.findOne({
    //   id: id,
    //   comments: {
    //     $elemMatch: {deleted: {$eq: false}}
    //   }
    // },
    // {
    //   "comments._id": 1,
    //   "comments.commentator": 1,
    //   "comments.helpful": 1,
    //   "comments.comment": 1,
    //   "comments.date": 1
    // }));
    if (err) {
      LOG.write("Database", `aggregate(line:18) failed because(${err}).`);
      return res.sendStatus(503);
    }
    return res.json(results);
  } else {
    return res.sendStatus(503);
  }
};

const addNewComment = async (req, res) => {
  const IlRank         = require("../assets/srcs/IlRank");
  const id           = req.params.id;
  const illust_model = require("../models/IllustrationModel");
  const user_model = require("../models/UserModel");
  const user         = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  const timestamp    = Date.now();
  const IlRankObj = new IlRank();
  let err, illustData, illustratorData, viewer, popularity;
  if (req.body.comment === undefined || req.body.comment === null) return res.sendStatus(406);
  if (user) {
    if (DBC.connect()) {
      [err, illustData] = await to(
        illust_model.findOne({ comments_box_id: id }, "uid illustrator popularity release_date")
      );
      if (err || illustData === null) {
        LOG.write(
          "Database",
          `failed to query Illustration data from comment box[${id}] because(${err}).`
        );
        return res.sendStatus(503);
      }
      if(user) viewer = user.uid === illustData.illustrator ? "owner" : "user";
      else viewer = "guest";
      popularity = IlRankObj.popularity(viewer, "comment", illustData.release_date.getTime());
      [err, illustratorData] = await to(
        user_model.findOne({ uid: illustData.illustrator }, "rank")
      );
      if (err || illustratorData === null) {
        LOG.write(
          "Database",
          `failed to query Illustration data from comment box[${id}] because(${err}).`
        );
        return res.sendStatus(503);
      }
      try {
        await illust_model.updateOne(
          { uid: illustData.uid },
          { popularity: illustData.popularity + popularity }
        );
        await user_model.updateOne(
          { uid: illustData.illustrator },
          { rank: illustratorData.rank + popularity }
        );
      } catch (e) {
        LOG.write("Database", "Can't update popularity or rank from comment method");
        return res.sendStatus(503);
      }
      [err] = await to(
        MODEL.updateOne(
          { id: id },
          {
            $push: {
              comments: {
                commentator: user.uid,
                helpful    : 0,
                comment    : req.body.comment,
                deleted    : false,
                date       : new Date(timestamp)
              }
            }
          }
        )
      );
      
      if (err) {
        LOG.write(
          "Database",
          `failed to add new comment to comment box[${id}] because(${err}).`
        );
        return res.sendStatus(503);
      }
      return res.sendStatus(201);
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

const delCommentBox = async (req, res) => {
  const id   = req.params.id;
  const user = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  let err;

  if (user.uid === 0) {
    if (DBC.connect()) {
      [err] = await to(MODEL.deleteOne({ id: id }));
      if (err) {
        LOG.write(
          "Database",
          `failed to delete comment box[${id}] because(${err}).`
        );
        return res.sendStatus(503);
      }
      return res.sendStatus(204);
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

const totalComments = async (req, res) => {
  const id = req.params.id;
  let err, tot;

  if (DBC.connect()) {
    [err, tot] = await to(
      MODEL.aggregate([
        { $match: { id: id } },
        {
          $project: {
            comments: {
              $size: "$comments"
            }
          }
        }
      ])
    );
    
    if (err) {
      LOG.write(
        "Database",
        `failed to find number of comment in comment box[${id}] because(${err}).`
      );
      return res.sendStatus(503);
    }
    return res.json({ total: tot[0].comments });
  } else {
    return res.sendStatus(503);
  }
};

const latestComment = async (req, res) => {
  const id = req.params.id;
  let err, comment, user;

  if (DBC.connect()) {
    [err, comment] = await to(
      MODEL.aggregate([
        { $match: { id: id } },
        {
          $project: {
            comments: {
              $slice: ["$comments", -1]
            }
          }
        }
      ])
    );
    
    if (err) {
      LOG.write(
        "Database",
        `failed to query latest comment in comment box[${id}] because (${err}).`
      );
      return res.sendStatus(503);
    }
    [err, user] = await to(USER_MODEL.findOne({ uid: comment[0].commentator }, "penname avatar"));
    if(err || user === null) return res.json({});
    return res.json({
      commentatorPenname: user.penname,
      commentatorAvatar : user.avatar,
      commentator       : comment[0].comments[0].commentator,
      helpful           : comment[0].comments[0].helpful,
      comment           : comment[0].comments[0].comment,
      date              : comment[0].comments[0].date,
      _id               : comment[0].comments[0]._id,
    });
  } else {
    return res.sendStatus(503);
  }
};

const delComment = async (req, res) => {
  const user       = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  const id         = req.params.id;
  const comment_id = req.params.commentId;

  if (user) {
    if (DBC.connect()) {
      //read more: https://docs.mongodb.com/manual/reference/operator/update/positional/#up._S_
      MODEL.update(
        { id: id, "comments._id": new MONGOOSE.Types.ObjectId(comment_id) },
        { $set: { "comments.$.deleted": true } },
        (err, count) => {
          if (err) {
            return res.sendStatus(503);
          }
        }
      );
      
      return res.sendStatus(200);
    } else {
      return res.sendStatus(503);
    }
  } else {
    return res.sendStatus(401);
  }
};

const addHelpful = async (req, res) => {
  const user       = req.token !== null ? AUTHENICATION.authenicate(req.token) : null;
  const id         = req.params.id;
  const comment_id = req.params.commentId;
  let err, comment;

  if (user) {
    if (DBC.connect()) {
      //read more: https://docs.mongodb.com/manual/reference/operator/update/positional/#up._S_
      comment = await MODEL.aggregate(
        [
          { $unwind: "$comments" },
          {
            $match: {
              id: id,
              "comments._id": new MONGOOSE.Types.ObjectId(comment_id)
            }
          },
          {
            $project: {
              helpful: "$comments.helpful"
            }
          }
        ],
        (err, data) => {
          if (err) return res.sendStatus(503);
          else return data;
        }
      );
      await MODEL.update(
        {
          id: id,
          "comments._id": new MONGOOSE.Types.ObjectId(comment_id)
        },
        { $set: { "comments.$.helpful": comment[0].helpful + 1 } },
        (err, count) => {
          if (err) return res.sendStatus(503);
        }
      );
      
      return res.sendStatus(200);
    } else {
      return res.sendStatus(503);
    }
  } else {
    return res.sendStatus(401);
  }
};

module.exports = {
  getAllComments,
  addNewComment,
  delCommentBox,
  totalComments,
  latestComment,
  delComment,
  addHelpful
};
