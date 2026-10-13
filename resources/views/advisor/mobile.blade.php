@php
    $character = asset('images/ai-advisor/a_polished_studio_like_promotional_brand_styled (1).png') . '?v=20261011-2';
    $characterVideo = asset('videos/ai-advisor/palaz-advisor.mp4') . '?v=20261012-1';
@endphp
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover,user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>مشاور هوشمند پالاز</title>
    <!-- Palaz Advisor Mobile v2 -->
    <style>
        :root{--red:#b71929;--ink:#202327;--muted:#85888d;--paper:#fbfaf8}
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;background:#f4f1ee;color:var(--ink);font-family:Tahoma,"Segoe UI",sans-serif}
        body{overflow:hidden}
        button,input{font:inherit}
        .advisor{height:100dvh;height:100svh;min-height:0;display:flex;flex-direction:column;width:100%;max-width:680px;margin:auto;background:var(--paper);position:relative;overflow:hidden}
        .top{padding:calc(12px + env(safe-area-inset-top)) 16px 10px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eee9e5;background:rgba(255,255,255,.94);backdrop-filter:blur(14px);z-index:5}
        .brand{display:flex;align-items:center;gap:10px}
                .brand strong{display:block;font-size:14px}.brand small{display:flex;gap:5px;align-items:center;color:#8b8d91;font-size:9px;margin-top:4px}
        .dot{width:6px;height:6px;border-radius:50%;background:#35a36a}
        .close{width:38px;height:38px;border:1px solid #e8e2de;background:#fff;border-radius:50%;font-size:24px;color:#555}
        .stage{flex:0 0 auto;padding:clamp(4px,1.2svh,10px) 16px 2px;display:flex;flex-direction:column;align-items:center;min-height:0}
        /* Use the approved advisor image as-is: preserve her face, scarf, pose and original background. */
        .character{width:min(76vw, min(38svh, 330px));height:min(76vw, min(38svh, 330px));max-width:330px;max-height:330px;min-width:0;min-height:0;display:grid;place-items:center;position:relative;overflow:hidden;flex:0 0 auto}
        .character:before,.character:after{content:none!important;display:none!important}
        .character img,.character video{width:100%;height:100%;object-fit:contain;position:relative;z-index:2;transform-origin:50% 82%;animation:none!important;transform:none!important;display:block}
        .character .advisor-idle-image{position:absolute;inset:0;width:100%;height:100%;object-fit:contain;z-index:2;opacity:1;transition:opacity .12s ease}
        .character .advisor-video{position:absolute;inset:0;width:100%;height:100%;object-fit:contain;z-index:3;visibility:visible}
        .video-sound{position:absolute;z-index:4;bottom:8px;right:8px;border:1px solid rgba(255,255,255,.7);background:rgba(32,35,39,.82);color:#fff;border-radius:999px;padding:7px 10px;font-size:10px;cursor:pointer;box-shadow:0 3px 12px rgba(0,0,0,.16)}
        .video-sound:focus-visible{outline:2px solid var(--red);outline-offset:2px}
        .video-sound:disabled{opacity:.65;cursor:wait}

        /* Keep the portrait and its background fixed; animate only the character layer is not possible with a flattened image. */
                .status{text-align:center;margin-top:2px;font-size:11px;color:var(--muted);min-height:18px}
        .status b{color:var(--red)}
        .bars{height:17px;display:flex;align-items:center;justify-content:center;gap:3px;margin-top:3px}
        .bars i{width:3px;height:5px;background:var(--red);border-radius:4px;animation:bars .75s ease-in-out infinite;opacity:.55}
        .bars i:nth-child(2){animation-delay:.1s}.bars i:nth-child(3){animation-delay:.2s}.bars i:nth-child(4){animation-delay:.3s}.bars i:nth-child(5){animation-delay:.4s}
        .messages{flex:1 1 auto;min-height:0;overflow:auto;overscroll-behavior:contain;padding:8px 16px 10px;scroll-behavior:smooth}
        .message{display:flex;gap:8px;align-items:flex-end;margin:8px 0}
        .message.user{flex-direction:row-reverse}
        .bubble{max-width:82%;padding:11px 13px;border-radius:17px 17px 3px 17px;background:#fff;border:1px solid #eee8e3;box-shadow:0 5px 18px rgba(30,25,20,.045);font-size:12px;line-height:1.9;white-space:pre-wrap}
        .user .bubble{background:#25282c;color:#fff;border-color:#25282c;border-radius:17px 17px 17px 3px}
        .quick{display:flex;gap:7px;overflow:auto;padding:4px 16px 9px;scrollbar-width:none}
        .quick::-webkit-scrollbar{display:none}.quick button,.visualizer-cta{flex:0 0 auto;border:1px solid #e6ded9;background:#fff;color:#555;border-radius:999px;padding:8px 11px;font-size:10px;text-decoration:none}.visualizer-cta{background:#25282c;color:#fff;border-color:#25282c}
        .composer{padding:8px 12px calc(10px + env(safe-area-inset-bottom));background:rgba(255,255,255,.97);border-top:1px solid #eee8e3;display:flex;gap:8px;align-items:center}
        .mic{width:54px;height:54px;border:0;border-radius:18px;background:var(--red);color:#fff;display:grid;place-items:center;box-shadow:0 10px 25px rgba(183,25,41,.2);flex:0 0 54px;position:relative}
        .mic svg{width:23px;height:23px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round}
        .mic.listening{animation:pulse 1.15s ease-in-out infinite}
        .mic.listening:after{content:"";position:absolute;inset:-7px;border:2px solid rgba(183,25,41,.22);border-radius:22px;animation:ring 1.15s ease-out infinite}
        .input-wrap{flex:1;display:flex;gap:7px;background:#f5f2ef;border:1px solid #e8e1dc;border-radius:17px;padding:5px 6px 5px 5px}
        .input-wrap input{width:100%;min-width:0;border:0;outline:0;background:transparent;padding:9px;font-size:11px;color:var(--ink)}
        .send{width:38px;height:38px;border:0;border-radius:13px;background:#25282c;color:#fff;font-size:18px}
        .hint{text-align:center;font-size:8px;color:#aaa;padding:0 16px 4px;background:#fff}
        .back{border:0;background:transparent;color:#777;font-size:11px;padding:8px}
        .typing{display:inline-flex;gap:3px;padding:13px 15px}.typing i{width:5px;height:5px;background:#aaa;border-radius:50%;animation:typing .8s infinite}.typing i:nth-child(2){animation-delay:.12s}.typing i:nth-child(3){animation-delay:.24s}
        .product-recommendations{width:100%;margin:8px 0 12px;padding:0 2px}
        .product-recommendations-title{font-size:10px;font-weight:700;color:#555;margin:0 0 7px}
        .product-recommendations-list{display:flex;gap:9px;overflow-x:auto;padding:1px 1px 8px;scrollbar-width:thin;overscroll-behavior-x:contain}
        .product-card{display:block;flex:0 0 174px;min-width:0;overflow:hidden;border:1px solid #ebe4df;border-radius:15px;background:#fff;color:inherit;text-decoration:none;box-shadow:0 4px 14px rgba(30,25,20,.045)}
        .product-card img{display:block;width:100%;height:100px;object-fit:contain;background:#f7f4f1}
        .product-card-no-image{height:100px;display:grid;place-items:center;background:#f7f4f1;color:#aaa;font-size:10px}
        .product-card-body{padding:9px 10px 11px}
        .product-card-name{font-size:11px;font-weight:700;line-height:1.7;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .product-card-meta{font-size:9px;color:#85888d;line-height:1.8;margin-top:4px;min-height:16px}
        .product-card-price{font-size:10px;color:var(--red);font-weight:700;margin-top:6px;line-height:1.7}
        .advisor-actions{display:flex;flex-wrap:wrap;gap:7px;width:100%;margin:2px 0 10px}
        .advisor-action{display:inline-flex;align-items:center;justify-content:center;border:1px solid #e5d9d3;border-radius:999px;background:#fff;color:#8e1825;text-decoration:none;font-size:10px;padding:8px 12px}
        /* Portrait stays unchanged; subtle motion follows the conversation state. */
        @keyframes float{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-4px) scale(1.02)}}
        @keyframes halo{0%,100%{opacity:.42;transform:scale(.9)}50%{opacity:.72;transform:scale(.96)}}
        @keyframes ring-listen{0%{opacity:.7;transform:scale(.86)}70%,100%{opacity:.08;transform:scale(1.08)}}
        @keyframes halo-think{0%,100%{opacity:.3;transform:scale(.9)}50%{opacity:.75;transform:scale(.98)}}
        @keyframes ring-speak{0%,100%{opacity:.25;transform:scale(.9)}50%{opacity:.8;transform:scale(1.03)}}
        @keyframes advisorIdle{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-2px) scale(1.006)}}
        @keyframes advisorListen{0%,100%{transform:translateY(0) rotate(0) scale(1)}50%{transform:translateY(-2px) rotate(-.35deg) scale(1.008)}}
        @keyframes advisorThink{0%,100%{transform:translateY(0) rotate(0) scale(1)}50%{transform:translateY(-1px) rotate(.45deg) scale(1.006)}}
        @keyframes advisorSpeak{0%,100%{transform:translateY(0) scale(1)}35%{transform:translateY(-2px) scale(1.009)}70%{transform:translateY(1px) scale(.998)}}
        @keyframes bars{50%{height:15px;opacity:1}}
        @keyframes pulse{50%{transform:scale(1.035)}} @keyframes ring{to{transform:scale(1.18);opacity:0}}
        @keyframes typing{50%{transform:translateY(-3px)}}
        @media(min-width:681px){body{display:grid;place-items:center;min-height:100dvh}.advisor{height:min(900px,100dvh);height:min(900px,100svh);border:1px solid #e9e2dd;border-radius:22px;box-shadow:0 20px 80px rgba(30,25,20,.12)}}
        @media(max-height:720px){.character{width:min(60vw,28svh,250px);height:min(60vw,28svh,250px)}.stage{padding-top:3px}.bars{height:10px;margin-top:0}.status{font-size:10px}.quick{padding-bottom:5px}.composer{padding-top:6px;padding-bottom:calc(6px + env(safe-area-inset-bottom))}}
        @media(max-width:380px){.top{padding-inline:12px}.stage{padding-inline:8px}.character{width:min(72vw,34svh,260px);height:min(72vw,34svh,260px)}.messages{padding-inline:12px}.quick{padding-inline:12px}.bubble{font-size:11px}.mic{width:48px;height:48px;flex-basis:48px;border-radius:16px}}
        @media(prefers-reduced-motion:reduce){*{animation:none!important;scroll-behavior:auto!important}}
            /* Final cleanup: no duplicate avatar or secondary controls in advisor messages */
        .message .avatar,.message .palaz-ai-avatar,.message img.avatar,.message .speak,.message .speak-btn,.message .voice-btn,.message .consultation-btn,.message .advisor-btn{display:none!important}
        .message{gap:0!important}
    </style>
</head>
<body>
<div class="advisor" id="advisor">
    <header class="top">
        <div class="brand"><div><strong>مشاور هوشمند پالاز</strong><small><i class="dot"></i> آماده گفتگو</small></div></div>
        <button class="close" type="button" onclick="history.length>1?history.back():location.href='{{ route('home') }}'" aria-label="بازگشت">×</button>
    </header>
    <section class="stage" id="stage">
        <div class="character">
            <img class="advisor-idle-image" id="advisorIdleImage" src="{{ $character }}" alt="مشاور پالاز">
            <video class="advisor-video" id="advisorVideo" autoplay muted loop playsinline preload="auto" poster="{{ $character }}" aria-label="ویدیوی متحرک مشاور پالاز">
                <source src="{{ $characterVideo }}" type="video/mp4">
            </video>
            <button class="video-sound" id="voiceTest" type="button" aria-label="تست صدای زنده مشاور">🔊 تست صدای مشاور</button>
        </div>
        <div class="status" id="status">برای گفتگو تایپ کنید یا روی میکروفن بزنید 🎙</div>
        <div class="bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
    </section>
    <main class="messages" id="messages" aria-live="polite">
        <div class="message assistant"><div class="bubble">سلام وقت شما بخیر من مشاور پالاز هستم و آمادگی دارم پاسخگوی شما در زمینه محصولات پالاز باشم.</div></div>
    </main>
    <form class="composer" id="form" data-url="{{ route('advisor.chat') }}" data-speech-url="{{ route('advisor.speech') }}">
        <button class="mic" id="mic" type="button" aria-label="شروع گفتگو با صدا">
            <svg viewBox="0 0 24 24"><path d="M12 15.5a3.5 3.5 0 0 0 3.5-3.5V7a3.5 3.5 0 0 0-7 0v5a3.5 3.5 0 0 0 3.5 3.5Z"/><path d="M5.5 11.5a6.5 6.5 0 0 0 13 0M12 18v3M9 21h6"/></svg>
        </button>
        <div class="input-wrap"><input id="input" autocomplete="off" placeholder="یا اینجا بنویسید..."><button class="send" type="submit" aria-label="ارسال">↑</button></div>
    </form>
    <div class="hint" id="hint">برای مکالمه روی میکروفن بزنید یا سؤال خود را تایپ کنید.</div>
</div>
<script>
(() => {
    const root=document.getElementById('advisor'), stage=document.getElementById('stage'), status=document.getElementById('status');
    const messages=document.getElementById('messages'), form=document.getElementById('form'), input=document.getElementById('input'), mic=document.getElementById('mic'), hint=document.getElementById('hint');
    const advisorVideo=document.getElementById('advisorVideo'), voiceTest=document.getElementById('voiceTest');
    const character=@json($character); let history=[{role:'assistant',content:'سلام وقت شما بخیر من مشاور پالاز هستم و آمادگی دارم پاسخگوی شما در زمینه محصولات پالاز باشم.'}]; let recognition=null; let speaking=false; let busy=false;

    const setState=(state,label)=>{
        root.classList.remove('listening','thinking','speaking'); if(state) root.classList.add(state);
        status.textContent=label;
    };
    const scroll=()=>messages.scrollTop=messages.scrollHeight;
    const add=(text,role)=>{
        const row=document.createElement('div'); row.className='message '+role;
        const b=document.createElement('div');b.className='bubble';b.textContent=text;row.appendChild(b);messages.appendChild(row);scroll();return b;
    };
    const addProducts=(products)=>{
        if(!Array.isArray(products)||!products.length)return;
        const section=document.createElement('section');
        section.className='product-recommendations';
        const title=document.createElement('div');
        title.className='product-recommendations-title';
        title.textContent='گزینه‌های مرتبط از کاتالوگ پالاز';
        section.appendChild(title);
        const list=document.createElement('div');
        list.className='product-recommendations-list';
        products.slice(0,3).forEach(product=>{
            if(!product||!product.url)return;
            const card=document.createElement('a');
            card.className='product-card';
            card.href=product.url;
            const imageUrl=typeof product.image==='string'?product.image.trim():'';
            if(imageUrl){
                const img=document.createElement('img');
                img.src=imageUrl;img.alt=product.name||'محصول پالاز';img.loading='lazy';
                img.onerror=()=>{const fallback=document.createElement('div');fallback.className='product-card-no-image';fallback.textContent='تصویر در دسترس نیست';img.replaceWith(fallback)};
                card.appendChild(img);
            }else{
                const placeholder=document.createElement('div');placeholder.className='product-card-no-image';placeholder.textContent='تصویر در دسترس نیست';card.appendChild(placeholder);
            }
            const body=document.createElement('div');body.className='product-card-body';
            const name=document.createElement('div');name.className='product-card-name';name.textContent=product.name||'محصول پالاز';body.appendChild(name);
            const meta=document.createElement('div');meta.className='product-card-meta';
            meta.textContent=[product.model?'مدل '+product.model:'',product.code?'کد '+product.code:''].filter(Boolean).join(' · ')||'جزئیات محصول';
            body.appendChild(meta);
            const price=document.createElement('div');price.className='product-card-price';price.textContent=product.price_label||'قیمت نیازمند استعلام';body.appendChild(price);
            card.appendChild(body);list.appendChild(card);
        });
        if(!list.children.length)return;
        section.appendChild(list);
        const row=document.createElement('div');row.className='message assistant';row.appendChild(section);
        messages.appendChild(row);scroll();
    };
    const typing=()=>{const row=document.createElement('div');row.className='message assistant';row.id='typing';row.innerHTML='<div class="bubble typing"><i></i><i></i><i></i></div>';messages.appendChild(row);scroll()};
    const addActions=(actions)=>{
        if(!Array.isArray(actions)||!actions.length)return;
        const wrap=document.createElement('div');wrap.className='advisor-actions';
        actions.slice(0,3).forEach(action=>{
            if(!action||!action.url||!action.label)return;
            const link=document.createElement('a');link.className='advisor-action';link.href=action.url;link.textContent=action.label;wrap.appendChild(link);
        });
        if(!wrap.children.length)return;
        const row=document.createElement('div');row.className='message assistant';row.appendChild(wrap);messages.appendChild(row);scroll();
    };
    const removeTyping=()=>document.getElementById('typing')?.remove();

    let activeSpeechAudio=null, activeSpeechUrl=null, speechGeneration=0;

    const stopSpeech=()=>{
        speechGeneration++;
        if(activeSpeechAudio){
            activeSpeechAudio.onplay=null;activeSpeechAudio.onended=null;activeSpeechAudio.onerror=null;
            try{activeSpeechAudio.pause();activeSpeechAudio.src='';activeSpeechAudio.load()}catch(e){}
            activeSpeechAudio=null;
        }
        if(activeSpeechUrl){URL.revokeObjectURL(activeSpeechUrl);activeSpeechUrl=null}
        speaking=false;root.classList.remove('speaking');
        advisorVideo.muted=true;advisorVideo.loop=true;advisorVideo.style.visibility='visible';
        const idleImage=document.getElementById('advisorIdleImage');
        if(idleImage)idleImage.style.opacity='0';
        const videoPlay=advisorVideo.play();
        if(videoPlay&&typeof videoPlay.catch==='function')videoPlay.catch(()=>{});
    };

    const finishSpeech=(generation,label)=>{
        if(generation!==speechGeneration)return;
        const audio=activeSpeechAudio,url=activeSpeechUrl;
        activeSpeechAudio=null;activeSpeechUrl=null;
        if(audio){
            audio.onplay=null;audio.onended=null;audio.onerror=null;
            try{audio.pause();audio.src='';audio.load()}catch(e){}
        }
        if(url)URL.revokeObjectURL(url);
        speaking=false;root.classList.remove('speaking');
        advisorVideo.muted=true;advisorVideo.loop=true;advisorVideo.style.visibility='visible';
        const idleImage=document.getElementById('advisorIdleImage');
        if(idleImage)idleImage.style.opacity='0';
        const videoPlay=advisorVideo.play();
        if(videoPlay&&typeof videoPlay.catch==='function')videoPlay.catch(()=>{});
        setState('',label);
    };

    const speak=async(text)=>{
        if(!text)return false;
        stopSpeech();
        const generation=speechGeneration;
        setState('thinking','پاسخ صوتی در حال آماده‌سازی است...');
        const speechController=new AbortController();
        const speechTimeout=setTimeout(()=>speechController.abort(),18000);
        try{
            const r=await fetch(form.dataset.speechUrl,{
                method:'POST',
                headers:{
                    'Content-Type':'application/json',
                    'Accept':'audio/mpeg,application/json',
                    'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
                },
                body:JSON.stringify({text}),
                signal:speechController.signal
            });
            clearTimeout(speechTimeout);
            if(generation!==speechGeneration)return false;
            if(!r.ok){
                const error=await r.json().catch(()=>({}));
                console.warn('[Palaz Advisor speech]',error.message||r.status);
                const label=r.status===503
                    ?'سرویس صدای مشاور تنظیم نشده یا موقتاً در دسترس نیست.'
                    :'ساخت صدای مشاور انجام نشد؛ دوباره امتحان کنید.';
                finishSpeech(generation,label);
                return false;
            }
            const blob=await r.blob();
            if(generation!==speechGeneration)return false;
            if(blob.size<128){
                finishSpeech(generation,'آماده شنیدن شما 🎙');
                return false;
            }

            activeSpeechUrl=URL.createObjectURL(blob);
            const audio=new Audio(activeSpeechUrl);
            activeSpeechAudio=audio;
            audio.preload='auto';
            audio.volume=1;
            audio.onplay=()=>{
                if(generation!==speechGeneration)return;
                speaking=true;
                setState('speaking','مشاور پالاز در حال صحبت است...');
                advisorVideo.muted=true;
                advisorVideo.loop=true;
                try{advisorVideo.currentTime=0}catch(e){}
                const idleImage=document.getElementById('advisorIdleImage');
                if(idleImage)idleImage.style.opacity='0';
                advisorVideo.style.visibility='visible';
                const videoPlay=advisorVideo.play();
                if(videoPlay&&typeof videoPlay.catch==='function')videoPlay.catch(()=>{});
            };
            audio.onended=()=>finishSpeech(generation,'آماده شنیدن شما 🎙');
            audio.onerror=()=>finishSpeech(generation,'آماده شنیدن شما 🎙');
            try{
                await audio.play();
                return true;
            }catch(e){
                console.warn('[Palaz Advisor audio playback]',e?.name||'playback_error');
                finishSpeech(generation,'آماده شنیدن شما 🎙');
                return false;
            }
        }catch(e){
            clearTimeout(speechTimeout);
            console.warn('[Palaz Advisor speech request]',e?.message||'request_error');
            finishSpeech(generation,e?.name==='AbortError'
                ?'پاسخ سرویس صدا بیش از حد طول کشید؛ تنظیمات سرویس را بررسی کنید.'
                :'ارتباط با سرویس صدای مشاور برقرار نشد.');
            return false;
        }
    };

    const send=async(text)=>{
        if(!text||busy)return;
        stopSpeech();
        busy=true;
        add(text,'user');
        history.push({role:'user',content:text});
        typing();
        setState('thinking','دارم فکر می‌کنم...');
        let replyToSpeak='';
        try{
            const r=await fetch(form.dataset.url,{
                method:'POST',
                headers:{
                    'Content-Type':'application/json',
                    'Accept':'application/json',
                    'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
                },
                body:JSON.stringify({message:text,messages:history.slice(0,-1).slice(-20),context:{surface:'mobile_advisor'}})
            });
            const data=await r.json();
            if(!r.ok||!data.reply)throw new Error('advisor');
            removeTyping();
            add(data.reply,'assistant');
            addProducts(data.products);
            addActions(data.actions);
            history.push({role:'assistant',content:data.reply});
            replyToSpeak=data.reply;
            setState('thinking','پاسخ آماده شد؛ صدای مشاور آماده می‌شود...');
        }catch(e){
            removeTyping();
            replyToSpeak='ارتباط با مشاور برقرار نشد. لطفاً یک بار دیگر امتحان کنید.';
            add(replyToSpeak,'assistant');
            setState('','آماده شنیدن شما 🎙');
        }finally{
            busy=false;
        }
        if(replyToSpeak)speak(replyToSpeak);
    };

    form.addEventListener('submit',e=>{e.preventDefault();const v=input.value.trim();if(!v)return;input.value='';send(v)});

    const welcomeText='سلام وقت شما بخیر، من مشاور پالاز هستم و آمادگی دارم پاسخگوی شما در زمینه محصولات پالاز باشم.';
    let voiceTestBusy=false;
    voiceTest.addEventListener('click',async()=>{
        if(voiceTestBusy)return;
        voiceTestBusy=true;
        voiceTest.disabled=true;
        voiceTest.textContent='در حال آماده‌سازی صدا...';
        try{
            await speak(welcomeText);
        }finally{
            voiceTest.disabled=false;
            voiceTest.textContent='🔊 تست صدای مشاور';
            voiceTestBusy=false;
        }
    });

    const startVoice=()=>{
        const SR=window.SpeechRecognition||window.webkitSpeechRecognition;
        if(!SR){
            hint.textContent='ورودی صوتی در این مرورگر در دسترس نیست؛ می‌توانید سؤال را تایپ کنید.';
            input.focus();
            return;
        }
        stopSpeech();
        if(recognition){try{recognition.stop()}catch(e){}recognition=null;}
        recognition=new SR();
        recognition.lang='fa-IR';
        recognition.interimResults=false;
        recognition.maxAlternatives=1;
        mic.classList.add('listening');
        setState('listening','گوش می‌دهم... 🎙');
        recognition.onresult=e=>{
            const text=e.results?.[0]?.[0]?.transcript?.trim();
            if(text){input.value=text;send(text);}
        };
        recognition.onerror=e=>{
            mic.classList.remove('listening');
            setState('','دوباره امتحان کنید 🎙');
            hint.textContent=e.error==='not-allowed'
                ?'اجازه میکروفن داده نشد؛ دسترسی میکروفن مرورگر را فعال کنید.'
                :'گفتگو با صدا در دسترس نبود؛ دوباره بزنید یا سؤال را تایپ کنید.';
        };
        recognition.onend=()=>{
            mic.classList.remove('listening');
            if(!busy&&!speaking)setState('','آماده شنیدن شما 🎙');
        };
        try{recognition.start();}
        catch(e){
            mic.classList.remove('listening');
            setState('','برای شروع دوباره روی میکروفن بزنید.');
        }
    };
    mic.addEventListener('click',startVoice);

    // The pre-made clip supplies motion only. It is always muted and plays in a loop
    // while the newly generated Persian voice is speaking.
    advisorVideo.muted=true;
    advisorVideo.loop=false;
    // Keep the advisor video looping silently, independently of online speech synthesis.
    advisorVideo.muted=true;
    advisorVideo.loop=true;
    advisorVideo.style.visibility='visible';
    const idleImage=document.getElementById('advisorIdleImage');
    if(idleImage)idleImage.style.opacity='0';
    advisorVideo.addEventListener('error',()=>{
        advisorVideo.style.visibility='hidden';
        if(idleImage)idleImage.style.opacity='1';
    });
    const initialVideoPlay=advisorVideo.play();
    if(initialVideoPlay&&typeof initialVideoPlay.catch==='function')initialVideoPlay.catch(()=>{
        advisorVideo.style.visibility='hidden';
        if(idleImage)idleImage.style.opacity='1';
    });
    setState('','آماده شنیدن شما 🎙');
})();
</script>
</body>
</html>