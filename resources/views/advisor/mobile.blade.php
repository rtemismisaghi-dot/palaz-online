@php
    $character = asset('images/ai-advisor/ChatGPT Image Sep 29, 2026, 03_52_25 PM.png');
@endphp
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover,user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>مشاور هوشمند پالاز</title>
    <style>
        :root{--red:#b71929;--ink:#202327;--muted:#85888d;--paper:#fbfaf8}
        *{box-sizing:border-box}
        html,body{margin:0;min-height:100%;background:#f4f1ee;color:var(--ink);font-family:Tahoma,"Segoe UI",sans-serif}
        body{overflow:hidden}
        button,input{font:inherit}
        .advisor{height:100dvh;min-height:100svh;display:flex;flex-direction:column;max-width:680px;margin:auto;background:var(--paper);position:relative;overflow:hidden}
        .top{padding:calc(12px + env(safe-area-inset-top)) 16px 10px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eee9e5;background:rgba(255,255,255,.94);backdrop-filter:blur(14px);z-index:5}
        .brand{display:flex;align-items:center;gap:10px}
                .brand strong{display:block;font-size:14px}.brand small{display:flex;gap:5px;align-items:center;color:#8b8d91;font-size:9px;margin-top:4px}
        .dot{width:6px;height:6px;border-radius:50%;background:#35a36a}
        .close{width:38px;height:38px;border:1px solid #e8e2de;background:#fff;border-radius:50%;font-size:24px;color:#555}
        .stage{padding:14px 16px 4px;display:flex;flex-direction:column;align-items:center}
        .character{width:min(48vw,230px);height:min(48vw,230px);max-height:230px;display:grid;place-items:center;position:relative}
        .character:before,.character:after{content:"";position:absolute;border-radius:50%;inset:8%;border:1px solid rgba(183,25,41,.12);transform:scale(.9);opacity:.55}
        .character:after{inset:2%;border-color:rgba(183,25,41,.06);transform:scale(.8)}
        .character img{width:100%;height:100%;object-fit:contain;position:relative;z-index:2;animation:float 2.8s ease-in-out infinite}
        .status{text-align:center;margin-top:2px;font-size:11px;color:var(--muted);min-height:18px}
        .status b{color:var(--red)}
        .bars{height:17px;display:flex;align-items:center;justify-content:center;gap:3px;margin-top:3px}
        .bars i{width:3px;height:5px;background:var(--red);border-radius:4px;animation:bars .75s ease-in-out infinite;opacity:.55}
        .bars i:nth-child(2){animation-delay:.1s}.bars i:nth-child(3){animation-delay:.2s}.bars i:nth-child(4){animation-delay:.3s}.bars i:nth-child(5){animation-delay:.4s}
        .messages{flex:1;overflow:auto;padding:8px 16px 10px;scroll-behavior:smooth}
        .message{display:flex;gap:8px;align-items:flex-end;margin:8px 0}
        .message.user{flex-direction:row-reverse}
        .avatar{width:32px;height:32px;border-radius:50%;overflow:hidden;flex:0 0 32px;background:#27292d;color:#fff;display:grid;place-items:center;font-size:8px}
        .avatar img{width:100%;height:100%;object-fit:cover}
        .bubble{max-width:82%;padding:11px 13px;border-radius:17px 17px 3px 17px;background:#fff;border:1px solid #eee8e3;box-shadow:0 5px 18px rgba(30,25,20,.045);font-size:12px;line-height:1.9;white-space:pre-wrap}
        .user .bubble{background:#25282c;color:#fff;border-color:#25282c;border-radius:17px 17px 17px 3px}
        .quick{display:flex;gap:7px;overflow:auto;padding:4px 16px 9px;scrollbar-width:none}
        .quick::-webkit-scrollbar{display:none}.quick button{flex:0 0 auto;border:1px solid #e6ded9;background:#fff;color:#555;border-radius:999px;padding:8px 11px;font-size:10px}
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
        .speaking .character img{animation:speak 1.1s ease-in-out infinite}.listening .character img{animation:listen 1s ease-in-out infinite}.thinking .character img{animation:think .75s ease-in-out infinite}
        @keyframes float{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-4px) scale(1.02)}}
        @keyframes listen{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-5px) scale(1.035)}}
        @keyframes think{0%,100%{transform:rotate(0) scale(1)}50%{transform:rotate(1deg) scale(1.025)}}
        @keyframes speak{0%,100%{transform:translateY(0) scale(1)}35%{transform:translateY(-3px) scale(1.03)}70%{transform:translateY(1px) scale(.995)}}
        @keyframes bars{50%{height:15px;opacity:1}}
        @keyframes pulse{50%{transform:scale(1.035)}} @keyframes ring{to{transform:scale(1.18);opacity:0}}
        @keyframes typing{50%{transform:translateY(-3px)}}
        @media(min-width:681px){body{display:grid;place-items:center}.advisor{height:min(900px,100dvh);border-left:1px solid #e9e2dd;border-right:1px solid #e9e2dd;box-shadow:0 20px 80px rgba(30,25,20,.12)}}
        @media(prefers-reduced-motion:reduce){*{animation:none!important;scroll-behavior:auto!important}}
    </style>
</head>
<body>
<div class="advisor" id="advisor">
    <header class="top">
        <div class="brand"><div><strong>مشاور هوشمند پالاز</strong><small><i class="dot"></i> آماده گفتگو</small></div></div>
        <button class="close" type="button" onclick="history.length>1?history.back():location.href='{{ route('home') }}'" aria-label="بازگشت">×</button>
    </header>
    <section class="stage" id="stage">
        <div class="character"><img src="{{ $character }}" alt="مشاور پالاز"></div>
        <div class="status" id="status">سلام، با صدای خودتان شروع کنید <b>🎙</b></div>
        <div class="bars" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
    </section>
    <main class="messages" id="messages" aria-live="polite">
        <div class="message assistant"><div class="avatar"><img src="{{ $character }}" alt=""></div><div class="bubble">سلام 👋 من مشاور هوشمند پالاز هستم.
هر سؤالی دارید بپرسید؛ برای انتخاب کفپوش، مقایسه، طراحی فضا و مسیر اجرا تخصصی راهنمایی‌تان می‌کنم.</div></div>
    </main>
    <div class="quick" id="quick">
        <button data-message="برای پذیرایی چه کفپوشی پیشنهاد می‌دهید؟">برای پذیرایی</button>
        <button data-message="بین موکت و لمینت کمکم کنید انتخاب کنم.">مقایسه موکت و لمینت</button>
        <button data-message="برای فضای من ایده طراحی بدهید.">ایده طراحی</button>
    </div>
    <form class="composer" id="form" data-url="{{ route('advisor.chat') }}">
        <button class="mic" id="mic" type="button" aria-label="شروع گفتگو با صدا">
            <svg viewBox="0 0 24 24"><path d="M12 15.5a3.5 3.5 0 0 0 3.5-3.5V7a3.5 3.5 0 0 0-7 0v5a3.5 3.5 0 0 0 3.5 3.5Z"/><path d="M5.5 11.5a6.5 6.5 0 0 0 13 0M12 18v3M9 21h6"/></svg>
        </button>
        <div class="input-wrap"><input id="input" autocomplete="off" placeholder="یا اینجا بنویسید..."><button class="send" type="submit" aria-label="ارسال">↑</button></div>
    </form>
    <div class="hint" id="hint">برای مکالمه صوتی روی میکروفن بزنید. مرورگر برای اولین بار اجازه دسترسی می‌خواهد.</div>
</div>
<script>
(() => {
    const root=document.getElementById('advisor'), stage=document.getElementById('stage'), status=document.getElementById('status');
    const messages=document.getElementById('messages'), form=document.getElementById('form'), input=document.getElementById('input'), mic=document.getElementById('mic'), quick=document.getElementById('quick'), hint=document.getElementById('hint');
    const character=@json($character); let history=[]; let recognition=null; let speaking=false; let busy=false;

    const setState=(state,label)=>{
        root.classList.remove('listening','thinking','speaking'); if(state) root.classList.add(state);
        status.textContent=label;
    };
    const scroll=()=>messages.scrollTop=messages.scrollHeight;
    const add=(text,role)=>{
        const row=document.createElement('div'); row.className='message '+role;
        const av=document.createElement('div'); av.className='avatar';
        if(role==='assistant'){const im=document.createElement('img');im.src=character;im.alt='';av.appendChild(im)}else av.textContent='شما';
        const b=document.createElement('div');b.className='bubble';b.textContent=text;row.append(av,b);messages.appendChild(row);scroll();return b;
    };
    const typing=()=>{const row=document.createElement('div');row.className='message assistant';row.id='typing';row.innerHTML='<div class="avatar"><img src="'+character+'" alt=""></div><div class="bubble typing"><i></i><i></i><i></i></div>';messages.appendChild(row);scroll()};
    const removeTyping=()=>document.getElementById('typing')?.remove();

    let voicesReady=false;
    const loadVoices=()=>{
        if(!('speechSynthesis' in window)) return [];
        const voices=speechSynthesis.getVoices();
        voicesReady=voices.length>0;
        return voices;
    };
    const persianVoice=()=>{
        const voices=loadVoices();
        if(!voices.length) return null;
        return voices.find(v=>/^fa(-|_)?IR$/i.test(v.lang))
            || voices.find(v=>/^fa/i.test(v.lang))
            || voices.find(v=>/persian|farsi|iran/i.test(v.name))
            || null;
    };
    const speak=async(text)=>{
        if(!('speechSynthesis' in window)||!text) return false;
        speechSynthesis.cancel();

        if(!voicesReady){
            loadVoices();
            await new Promise(resolve=>setTimeout(resolve,120));
        }

        const u=new SpeechSynthesisUtterance(text);
        const v=persianVoice();
        u.lang=v?.lang||'fa-IR';
        if(v) u.voice=v;
        u.rate=.92;
        u.pitch=1.03;
        u.volume=1;

        return await new Promise(resolve=>{
            let started=false;
            u.onstart=()=>{
                started=true;
                speaking=true;
                setState('speaking','مشاور پالاز در حال صحبت است...');
            };
            u.onend=()=>{
                speaking=false;
                setState('','آماده شنیدن شما 🎙');
                resolve(true);
            };
            u.onerror=()=>{
                speaking=false;
                setState('','برای پخش صدا، یک بار روی صفحه لمس کنید و دوباره امتحان کنید.');
                resolve(false);
            };
            speechSynthesis.speak(u);
            setTimeout(()=>{
                if(!started && !speechSynthesis.speaking){
                    setState('','برای فعال کردن صدای مشاور یک بار روی صفحه لمس کنید.');
                    resolve(false);
                }
            },900);
        });
    };

    const send=async(text)=>{
        if(!text||busy)return; busy=true; add(text,'user'); history.push({role:'user',content:text}); quick.style.display='none'; typing(); setState('thinking','دارم فکر می‌کنم...');
        try{
            const r=await fetch(form.dataset.url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({message:text,messages:history.slice(-10),context:{surface:'mobile_advisor'}})});
            const data=await r.json(); if(!r.ok||!data.reply)throw new Error('advisor');
            removeTyping(); add(data.reply,'assistant'); history.push({role:'assistant',content:data.reply});
            setState('speaking','پاسخ شما آماده است...'); speak(data.reply);
        }catch(e){removeTyping();const msg='ارتباط با مشاور برقرار نشد. لطفاً یک بار دیگر امتحان کنید.';add(msg,'assistant');setState('','آماده شنیدن شما 🎙') }
        finally{busy=false}
    };

    form.addEventListener('submit',e=>{e.preventDefault();const v=input.value.trim();if(!v)return;input.value='';send(v)});
    quick.querySelectorAll('button').forEach(b=>b.addEventListener('click',()=>send(b.dataset.message)));

    const startVoice=()=>{
        const SR=window.SpeechRecognition||window.webkitSpeechRecognition;
        if(!SR){hint.textContent='این مرورگر ورودی صوتی را پشتیبانی نمی‌کند؛ از Chrome/Safari به‌روز استفاده کنید.';input.focus();return}
        if(speaking&&'speechSynthesis'in window){speechSynthesis.cancel();speaking=false}
        if(recognition){try{recognition.stop()}catch(e){} recognition=null}
        recognition=new SR(); recognition.lang='fa-IR';recognition.interimResults=false;recognition.maxAlternatives=1;
        mic.classList.add('listening');setState('listening','گوش می‌دهم... 🎙');
        try{recognition.start()}catch(e){mic.classList.remove('listening');setState('','برای شروع دوباره روی میکروفن بزنید.');return}
        recognition.onresult=e=>{const text=e.results?.[0]?.[0]?.transcript?.trim();if(text){input.value=text;send(text)}};
        recognition.onerror=e=>{mic.classList.remove('listening');setState('','دوباره امتحان کنید 🎙');hint.textContent=e.error==='not-allowed'?'اجازه میکروفن داده نشد؛ دسترسی Microphone مرورگر را فعال کنید.':'گفتگو با صدا در دسترس نبود؛ دوباره بزنید.'};
        recognition.onend=()=>{mic.classList.remove('listening');if(!busy&&!speaking)setState('','آماده شنیدن شما 🎙')};
    };
    mic.addEventListener('click',startVoice);

    if('speechSynthesis' in window){
        loadVoices();
        window.speechSynthesis.addEventListener('voiceschanged',loadVoices);

        // موبایل‌ها معمولاً پخش خودکار صدا را بدون تعامل کاربر مسدود می‌کنند.
        // اولین لمس کاربر صدای خوش‌آمد را فعال می‌کند و بعد از آن پاسخ‌ها صوتی خوانده می‌شوند.
        const welcome=()=>{
            document.removeEventListener('pointerdown',welcome);
            speak('سلام، من مشاور هوشمند پالاز هستم. برای انتخاب کفپوش مناسب کمکتان می‌کنم.');
        };
        document.addEventListener('pointerdown',welcome,{once:true});

        setTimeout(()=>{
            if(!speaking && !busy){
                setState('','برای شروع گفتگو روی میکروفن بزنید 🎙');
            }
        },800);
    }

})();
</script>
</body>
</html>