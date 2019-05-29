  /* ****************************************************************************************************************************************
   *   IlRank(Original from EdgeRank)
   *   Used for measure the popularity on illustraions
   *   By given: Rank(User) = Sum( Popularity(Ils) )
   *   Popularity(Il) = Ue * We * Gte
   *   e = every event that occurs to the illustration such as favorite button cliked, comment or view
   *   Ue = User affinity to that event that affect to the illustration popularity, such as User has more affinity than Guest or Illustrator
   *   We = Weight of event, different event have a differnt weight, such as weiht of view event is less than comment event
   *   Gte = Illustration growth trend, that depend on the release time of illustration the more time passed is mean the more values
   *         equal to [(ue * we)^(ln (currentTimestamp / ReleaseTimestamp))]
   *   In summerized:
   *                   Rank(User) = Sum( Ue*We*[(ue * we)^(ln (currentTimestamp / ReleaseTimestamp))] )
   *   if user have follower; 
   *                   Rank(User) = |followers| * Sum( Ue*We*[(ue * we)^(ln (currentTimestamp / ReleaseTimestamp))] )
   * **************************************************************************************************************************************** */

class IlRank {
    constructor (IlRankVarPath){
        const fs = require("fs");
        let IlRankVar;
        if(IlRankVarPath === undefined){
            const path = require("path");
            IlRankVar = JSON.parse(
                fs.readFileSync(path.join(__dirname, "../../") + "models/IlRankVar.json")
            );
        } else {
            IlRankVar = JSON.parse(
                fs.readFileSync(IlRankVarPath)
            );
        }
        this.IlRankVar = IlRankVar;
    }

    get affinity(){
        return this.IlRankVar.affinity;
    }

    get weight(){
        return this.IlRankVar.weight;
    }

    /**
     *
     * @param {int} illustrator illustrator uid.
     * @param {float} popularity popularity point.
     * @returns {int} if success this method will automatically update illustrator rank and return a newRank
     */
    async rank(illustrator, popularity){
        // TODO : renovate this method
        const DBC           = require("../../controllers/MongooseConnect");
        const MODEL         = require("../../models/UserModel");
        let illustrator_data, newRank;

        if (illustrator === undefined || popularity === undefined) 
            throw "undefined params";
        else {
            if(DBC.connect()){
                illustrator_data = await MODEL.aggregate([
                  { $match: { uid: illustrator } },
                  {
                    $project: {
                      nFollowers: {
                        $size: "$followers"
                      },
                      currentPopularity: "$popularity",
                      oldRank: "$rank"
                    }
                  }
                ],
                (err, data) => {
                    if (err) return false;
                    else return data;
                }
                );
                if(illustrator_data.length === 0) return false;
                if(illustrator_data === false) return false;
                else illustrator_data = illustrator_data[0];
                newRank = illustrator_data.oldRank + illustrator_data.nFollowers * popularity;
                try {
                    await MODEL.updateOne({ uid: illustrator }, { rank: newRank });
                    DBC.disconnect();
                } catch (err) {
                    return false;
                }
                return newRank;

            } else 
                return false;
        } 
    }

    /**
     *
     * @param {string} affnity Field-name inside obj affinity according to IlRankVar.json file
     * @param {string} weight Field-name inside obj weight according to IlRankVar.json file
     * @param {int} releaseTime Illustration release timestamp
     * @returns {int} popularity point
     */
    popularity(affinity, weight, releaseTime){
        const currentTimestamp = Date.now();
        const Ue = this.IlRankVar.affinity[affinity];
        const We = this.IlRankVar.weight[weight];
        if (Ue !== undefined && We !== undefined && releaseTime <= currentTimestamp) {
            if(Ue === 0) return 0;
            const Gte = Math.pow(Ue*We, Math.log(currentTimestamp / releaseTime));
            const popularity = Ue * We * Gte;
            return popularity;
        } else {
            throw "undefined params or invalid range of release timestamp";
        }
    }
}

module.exports = IlRank;