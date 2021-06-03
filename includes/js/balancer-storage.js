(function ($) {
    // TODO: REMOVER LOGS
    const WPPostsBalancer = {
        isAvailable: typeof(Storage) !== "undefined",
        maxPreferenceItems: postsBalancerData?.maxPreferenceItems ?? 30,
        userPreferencesLoaded: false,
        getLocalUserPreference: function(){
            const localPreferences = window.localStorage.getItem('taBalancerUserPreferences');
            return localPreferences ? JSON.parse(localPreferences) : null;
        },
        setLocalUserPreference: function(userPreferences){
            window.localStorage.setItem('taBalancerUserPreferences', JSON.stringify(userPreferences));
        },

        /**
        *   @method appendToLocalUserPreference
        *   It appends a set of balancer data to the localStorage stored one.
        *   @param {mixed[]} newPreferences                                     The preferences the append to the localStorage data
        */
        appendToLocalUserPreference: function(newPreferences){
            if(!newPreferences || !newPreferences.info)
                return;

            let updatedPreferences = this.getLocalUserPreference();

            if(!updatedPreferences || !updatedPreferences.info){
                updatedPreferences = newPreferences;
            }
            else if( newPreferences.info ){
                for (var preferenceSlug in newPreferences.info) {
                    if(!newPreferences.info.hasOwnProperty(preferenceSlug))
                        continue;

                    const newPreferenceIds = newPreferences.info[preferenceSlug];
                    // let updatedPreferenceIds = updatedPreferences.info[preferenceSlug];
                    if(!updatedPreferences.info[preferenceSlug]) // This preference is not stored in the localstorage, save all.
                        updatedPreferences.info[preferenceSlug] = newPreferenceIds;
                    else{
                        updatedPreferences.info[preferenceSlug] = updatedPreferences.info[preferenceSlug].concat(newPreferenceIds);
                        updatedPreferences.info[preferenceSlug] = updatedPreferences.info[preferenceSlug].filter( (id,index) => updatedPreferences.info[preferenceSlug].indexOf(id) == index ); // remove duplicates
                    }

                    if(this.maxPreferenceItems > 0 && updatedPreferences.info[preferenceSlug]?.length) {
                        difMax = updatedPreferences.info[preferenceSlug].length - this.maxPreferenceItems;
                        if(difMax > 0)
                            updatedPreferences.info[preferenceSlug] = updatedPreferences.info[preferenceSlug].slice(difMax);
                    }

                    // console.log(`UPDATED ${preferenceSlug}`, updatedPreferences.info[preferenceSlug]);
                }
            }

            this.setLocalUserPreference(updatedPreferences);
        },

        /**
        *   @method getUserPreference
        *   @return {mixed[]}
        *   Returns the user preferences. If it is logged in, returns the data sent
        *   to the client from postsBalancerData. If not logged, returns the
        *   localStorage data.
        */
        getUserPreference: function(){
            let { userPreferences, percentages, isLogged } = postsBalancerData;
            if( isLogged )
                return userPreferences; // from backend
            return this.getLocalUserPreference(); // from localStorage
        },

        /**
        *   @method loadUserPreferences
        *   @return {mixed[]|null}
        *   Prepares the user preference data for logged an not logged in users.
        */
        loadUserPreferences: async function(){
            if(this.userPreferencesLoaded)
                return this.getUserPreference();
            if(!this.isAvailable)
                return null;

            let { userPreferences, percentages, isLogged } = postsBalancerData;

            if(!isLogged)
                this.appendToLocalUserPreference(userPreferences); // appends to the prefences stored in local storage

            console.log('USER PREFERENCES', this.getUserPreference());
            this.userPreferencesLoaded = true;
            return await this.loadUserPreferences();
        },
    };

    window.postsBalancer = {
        loadPreferences: WPPostsBalancer.loadUserPreferences.bind(WPPostsBalancer),
        // getLocalPreferences: WPPostsBalancer.getLocalUserPreference.bind(WPPostsBalancer),
        // setLocalUserPreference: WPPostsBalancer.setLocalUserPreference.bind(WPPostsBalancer),
    };

    // WPPostsBalancer.loadUserPreferences();
})(jQuery);
