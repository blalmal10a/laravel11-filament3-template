<div>

    <div id="game-container" style="height: 100vh; width: 100vw;position: relative;">
        <div style="position: absolute; bottom: 0; left: 0; color: white; padding: 10px">
            <a href="https://chhutsiak.netlify.app" style="color: dodgerblue; font-weight: 500">
                Go to chhutsiak
            </a>
        </div>
        <div class="absolute top-2 right-20">
            <button
                class="focus:outline-none disabled:cursor-not-allowed disabled:opacity-75 flex-shrink-0 font-medium rounded-md text-sm gap-x-1.5 px-2.5 py-1.5 shadow-sm text-white dark:text-gray-900 bg-primary-500 hover:bg-primary-600 disabled:bg-primary-500 dark:bg-primary-400 dark:hover:bg-primary-500 dark:disabled:bg-primary-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500 dark:focus-visible:outline-primary-400 inline-flex items-center"
                @click="document.getElementById('game-container').style.display = 'none'">
                Hide this stupid game
            </button>
        </div>
        <iframe id="chhutsiak-iframe" width="100%" height="100%" src="https://chhutsiak.netlify.app"
            frameborder="0"></iframe>
    </div>
</div>
