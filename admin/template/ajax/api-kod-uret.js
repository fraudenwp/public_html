    function yenileApiKodu() {
        // Burada rastgele bir API kodu üretmek için JavaScript kullanabilirsiniz
        var randomCode = generateRandomCode(); // Örnek olarak, bu fonksiyonun nasıl çalıştığını göstereceğim

        // Yeni kodu input alanına yerleştirme
        document.getElementById('api_kodu').value = randomCode;
    }

    // Örnek rastgele kod üretme fonksiyonu
    function generateRandomCode() {
        var chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+';
        var length = 30;
        var randomCode = '';
        for (var i = 0; i < length; i++) {
            randomCode += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return randomCode;
    }