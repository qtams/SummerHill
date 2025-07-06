<!-- Tailwind Script -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="icon" href=
"images/Icon.png"
        type="image/x-icon" />
<!-- Icon Link-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>

<style>
    [x-cloak] { display: none !important; }
</style>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons();
</script>

<!-- Alpine Script-->
<script src="//unpkg.com/alpinejs" defer></script>
<!-- Excel Script-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>

<!-- Tailwind Modified code-->
<script>
    tailwind.config = {
      theme: {
        extend: {
          boxShadow: {
            btnShadow: 'rgba(0, 0, 0, 0.24) 0px 3px 8px',
            inputShadow:'rgba(0, 0, 0, 0.16) 0px 1px 4px',
            loginShadow: 'rgba(14, 30, 37, 0.12) 0px 2px 4px 0px, rgba(14, 30, 37, 0.32) 0px 2px 16px 0px',
            contentShadow:'rgba(0, 0, 0, 0.25) 0px 0.0625em 0.0625em, rgba(0, 0, 0, 0.25) 0px 0.125em 0.5em, rgba(255, 255, 255, 0.1) 0px 0px 0px 1px inset',
            headShadow: 'rgba(0, 0, 0, 0.45) 0px 25px 20px -20px',
            tableShadow: 'rgba(0, 0, 0, 0.05) 0px 0px 0px 1px, rgb(209, 213, 219) 0px 0px 0px 1px inset',
            msgShadow: 'rgba(0, 0, 0, 0.05) 0px 0px 0px 1px, rgb(209, 213, 219) 0px 0px 0px 1px inset',
            homeShadow:'rgba(14, 30, 37, 0.12) 0px 2px 4px 0px, rgba(14, 30, 37, 0.32) 0px 2px 16px 0px',
            sampleShadow:'rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset', 
            sideShadow: 'rgba(0, 0, 0, 0.16) 0px 4px 4px -2px',
            innerShadow: 'inset 0 2px 4px rgba(0, 0, 0, 0.06)',
  
            pressDownDeep: 'inset 3px 3px 6px rgba(0, 0, 0, 0.3), inset -3px -3px 6px rgba(255, 255, 255, 0.1)',

          },
          colors:{
            rowCol: '#040A44',
            dashColor: '#040A44',
            hovColor: '#212C98',
            Syellow:'#effe41',
            Sblue:'#1d2a61',
            Sdarkblue:'#051350',
            textColor:'#07144e',
            spryBlue: '#0096b1',
            
            spryOrange: '#ff914c',
            hyaGreen: '#1cc037',
            ueRed: '#ba0000',
            ccssGreen: '#13b03d',
            modifGray: '#eeeeee',
            modifGreen: '#0a4d2d'
          },
          width:{

            'max': "calc(100% - 16rem)",
            'min': "calc(100% - 7rem)",
          }
          
        }
      }
    }

    
  </script>