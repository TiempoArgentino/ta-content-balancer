(function ($) {
    // TODO: REMOVER LOGS
    const WPPostsBalancer = {
        isAvailable: typeof(Storage) !== "undefined",
        userPreferencesLoaded: false,
        getLocalUserPreference: function(){
            const localPreferences = window.localStorage.getItem('taBalancerUserPreferences');
            return localPreferences ? JSON.parse(localPreferences) : null;
        },
        setLocalUserPreference: function(userPreferences){
            window.localStorage.setItem('taBalancerUserPreferences', JSON.stringify(userPreferences));
        },
        appendToLocalUserPreference: function(newPreferences){
            if(!newPreferences)
                return;

            let updatedPreferences = this.getLocalUserPreference();
            
            if(!updatedPreferences){
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

                    // console.log(`UPDATED ${preferenceSlug}`, updatedPreferences.info[preferenceSlug]);
                }
            }

            this.setLocalUserPreference(updatedPreferences);
        },
        loadUserPreferences: async function(){
            if(this.userPreferencesLoaded)
                return this.getLocalUserPreference();
            if(!this.isAvailable)
                throw "noLocalStorage";

            console.log('balancerData', postsBalancerData);
            const { userPreferences, percentages, isLogged } = postsBalancerData;
            if(isLogged)
                this.setLocalUserPreference(userPreferences); // Overrides every preference stored in localstorage
            else
                this.appendToLocalUserPreference(userPreferences); // appends to the prefences stored in local storage

            console.log('USER PREFERENCES', this.getLocalUserPreference());
            this.userPreferencesLoaded = true;
            return this.getLocalUserPreference();
        },
    };

    window.postsBalancer = {
        loadPreferences: WPPostsBalancer.loadUserPreferences.bind(WPPostsBalancer),
        getLocalPreferences: WPPostsBalancer.getLocalUserPreference.bind(WPPostsBalancer),
        setLocalUserPreference: WPPostsBalancer.setLocalUserPreference.bind(WPPostsBalancer),
    };

    // WPPostsBalancer.loadUserPreferences();
})(jQuery);
