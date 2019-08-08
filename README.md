# passMusic

### Wave surfer default code

    <script>  
        var wavesurfer = WaveSurfer.create({
            container: "#waveforms'.$_track['id'].'",
            height: 67, barHeight: 1, waveColor: "#9a1d1d", 
            barGap: 4, barWidth: 2, progressColor: "#fbfafa"
        });
        wavesurfer.load("'.getAudio($_track['audio']).'");
        wavesurfer.setMute(true);
        wavesurfer.on("seek", function () {
            seeker("'.getAudio($_track['audio']).'", "'.$_track['id'].'", "'.$t_format.'", wavesurfer.getCurrentTime());
        }); 
    </script>


### New Wave surfer initialization code

    <div id="waveforms'.$track['id'].'"></div>
    <div id="real-play'.$track['id'].'" style="display: none;">0</div>
    <div id="wave_init" 
        data-track-url="'.getAudio($track['audio']).'" 
        data-track-id="'.$track['id'].'" 
        data-track-format="'.$t_format.'">
    </div>

### New Wave surfer initialization function

    $('#wave_init').each(function(){
      wavesurfer_in = $('#wave_init');
        // Generate unique id
        var wid = '_' + Math.random().toString(36).substr(2, 9);
        var id = $(wavesurfer_in).data('track-id');
        var audio = $(wavesurfer_in).data('track-url');
        var format = $(wavesurfer_in).data('track-format');
           
        // Initialize WaveSurfer
        var wavesurfer = WaveSurfer.create({
            container: '#waveform' + id,
            height: 67, barHeight: 1, waveColor: "#9a1d1d",  barGap: 4,
            barWidth: 2, progressColor: "#fbfafa"
        });
        
        // Load audio file
        wavesurfer.load(audio); 
        wavesurfer.setMute(true);
        wavesurfer.on("seek", function () {
            seeker(audio, id, format, wavesurfer.getCurrentTime());
        });
    });


*1. Ordered Italic List item 1*

*2. Ordered Italic List item 2*

*3. Ordered Italic List item 3*


*- Unordered Italic List item 1*

*- Unordered Italic List item 2*

*- Unordered Italic List item 3*

