const MODEL = require("../../models/CounterModel");
const DBC = require("../../controllers/MongooseConnect");

async function getNextSeq(modelName) {
  const seq = await MODEL.findById({ _id: modelName });
  await MODEL.updateOne({ _id: modelName }, { val: seq.val + 1 });
  return seq.val;
  /*if (DBC.connect()) {
    const seq = await MODEL.findById({ _id: modelName });
    await MODEL.updateOne({ _id: modelName }, { val: seq.val + 1 });
    DBC.disconnect();
    return seq.val;
  } else {
    DBC.disconnect();
    return false;
  }*/
}

async function decreaseSeq(modelName) {
  const seq = await MODEL.findById({ _id: modelName });
  await MODEL.updateOne({ _id: modelName }, { val: seq.val - 1 });
  return seq.val;
  /*if (DBC.connect()) {
    const seq = await MODEL.findById({ _id: modelName });
    await MODEL.updateOne({ _id: modelName }, { val: seq.val - 1 });
    DBC.disconnect();
    return seq.val;
  } else {
    DBC.disconnect();
    return false;
  }*/
}

module.exports = { getNextSeq, decreaseSeq };
