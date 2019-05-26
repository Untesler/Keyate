const fs = require("fs");
const path = require("path");
const topPath = path.join(__dirname, "../../");

async function categorize(imgFile) {
  const bayes        = require("classificator");
  let   classifyCate = JSON.parse(
    await fs.readFileSync(`${topPath}/models/ClassifyCateModel.json`)
  );
  let classifier = bayes({
    tokenizer: function(text) {
      return text.split(",");
    }
  });
  classifier = bayes.fromJson(classifyCate);
  let labelStr = "";

  const labels = await labelImg(imgFile);
  labels.forEach(label => {
    labelStr += `${label.description},`;
  });
  const category = classifier.categorize(labelStr);
  return [category, labels];
}

async function labelImg(imgFile) {
  //https://www.npmjs.com/package/classificator
  //https://www.npmjs.com/package/@google-cloud/vision
  const vision   = require("@google-cloud/vision");
  const client   = new vision.ImageAnnotatorClient();
  const [result] = await client.labelDetection(`${imgFile}`);
  const labels   = result.labelAnnotations;
  return labels;
}

async function tag(imgFile, numberOfTag = 5) {
  const excludeTag = JSON.parse(
    await fs.readFileSync(`${topPath}/models/excludeTag.json`)
  );
  const [category, labels] = await categorize(imgFile);
  let tag = "";
  let nLoop = numberOfTag;

  if (category.predictedCategory === "Background") {
    labels.forEach(label => {
      if (label.score > 0.7 && nLoop > 0) {
        if (excludeTag[label.description] === undefined) {
          tag += `${label.description},`;
          nLoop--;
        }
      }
    });
    if (tag.endsWith(",")) tag = tag.substr(0, tag.length - 1);
    if (numberOfTag === nLoop) return "Untagged able";
    return tag;
  } else {
    return "Untagged able";
  }
}

module.exports = {
    categorize,
    labelImg,
    tag
}
