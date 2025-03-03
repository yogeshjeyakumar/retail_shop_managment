<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/billing.css">
</head>

<body>
    <header>
        <div class="logo">WOOD FURNITURE</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="stocks.php">Stocks</a>
            <a href="customer.php">Customers</a>
        </nav>
    </header>

    <center>
        <h1>Billing System</h1>
        <div class="customer-details">
            <h3>Customer Details</h3>
            <form id="customer-form">
                <div class="customer_content">
                    <input type="text" id="customer-name" class="customer_body" placeholder="Customer Name" required><br>
                    <input type="text" id="customer-phone" class="customer_body" placeholder="Phone Number" required><br>
                    <input type="text" id="customer-address" class="customer_body" placeholder="Address" required><br>
                    <select id="payment-option" class="customer_body" required>
                        <option value="">Select Payment Option</option>
                        <option value="Cash">Cash</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="Online Payment">Online Payment</option>
                    </select><br>
                </div>
            </form>
        </div>
        <hr>
        <input type="text" id="search-box" placeholder="Search products..." onkeyup="searchProducts()">
        <div class="bill-list">
            <h3>Selected Products:</h3>
            <table id="bill-table" border="1">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="bill-list">
                    <!-- Bill items will be added here dynamically -->
                </tbody>
            </table>
            <button id="generate-bill">Generate Bill</button>
        </div>
        <div class="product-list" id="product-list">
            <?php
            $conn = mysqli_connect("localhost:3307", "root", "", "products");
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $tables = [
                'beds',
                'center_table',
                'chairs',
                'dining_table',
                'dressing_table',
                'showcase',
                'sofa',
                'wardrobe',
                'writing_table'
            ];

            foreach ($tables as $table_name) {
                $product_query = "SELECT *, '$table_name' AS table_name FROM $table_name";
                $product_result = $conn->query($product_query);

                while ($product = $product_result->fetch_assoc()) {
                    echo "<div class='product-item' id='product-" . $product['code'] . "' data-id='" . $product['code'] . "' data-category='" . $table_name . "'>
        <img src='" . $product['image'] . "' alt='" . $product['name'] . "' />
        <h3>" . $product['name'] . "</h3>
        <p>₹" . $product['price'] . "</p>
        <p>Stock: " . $product['quantity'] . "</p>
        <button class='add-to-bill' 
            data-code='" . $product['code'] . "' 
            data-name='" . $product['name'] . "' 
            data-price='" . $product['price'] . "' 
            data-quantity='" . $product['quantity'] . "' 
            data-table='" . $table_name . "'>
            Add
        </button>
    </div>";
                }
            }
            ?>
        </div>

        <footer>
            <p>&copy; 2025 WOOD FURNITURE. All Rights Reserved.</p>
        </footer>

        <script>
            let bill = [];
            document.querySelectorAll('.add-to-bill').forEach(button => {
                button.addEventListener('click', function() {
                    console.log('Product Added:', this.getAttribute('data-code'),
                        this.getAttribute('data-name'),
                        this.getAttribute('data-price'),
                        this.getAttribute('data-quantity'));

                    const product = {
                        code: this.getAttribute('data-code'),
                        name: this.getAttribute('data-name'),
                        price: parseFloat(this.getAttribute('data-price')),
                        quantity: 1,
                        stock: parseInt(this.getAttribute('data-quantity')),
                        table_name: this.getAttribute('data-table'),
                    };

                    console.log('Product Object:', product);
                    let existingProduct = bill.find(item => item.code === product.code);
                    console.log('Existing Product:', existingProduct);

                    if (existingProduct) {
                        if (existingProduct.quantity < product.stock) {
                            existingProduct.quantity++;
                        } else {
                            alert("Insufficient stock!");
                            return;
                        }
                    } else {
                        if (product.stock > 0) {
                            bill.push(product);
                        } else {
                            alert("Product is out of stock!");
                            return;
                        }
                    }

                    console.log('Updated Bill:', bill);
                    document.getElementById('product-' + product.code).style.backgroundColor = '#a8d7d3';
                    updateBill();
                });
            });

            function updateBill() {
                const billList = document.getElementById('bill-list');
                billList.innerHTML = '';

                bill.forEach(item => {
                    const listItem = document.createElement('tr');
                    listItem.innerHTML = `
            <td>${item.name}</td>
            <td>₹${item.price}</td>
            <td>
                <div class="quantity-btn-group">
                    <button class="quantity-btn decrease" data-id="${item.code}">-</button>
                    <span>${item.quantity}</span>
                    <button class="quantity-btn increase" data-id="${item.code}">+</button>
                </div>
            </td>
            <td>₹${item.price * item.quantity}</td>
            <td><button class="remove-btn" data-id="${item.code}">Remove</button></td>
        `;
                    billList.appendChild(listItem);
                });

                console.log('Bill List Updated:', billList.innerHTML);
                attachEvents();
            }

            function attachEvents() {
                document.querySelectorAll('.remove-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const productId = this.getAttribute('data-id');
                        bill = bill.filter(item => item.code !== productId);
                        updateBill();
                    });
                });
                document.querySelectorAll('.increase').forEach(button => {
                    button.addEventListener('click', function() {
                        const productId = this.getAttribute('data-id');
                        const product = bill.find(item => item.code === productId);
                        if (product && product.quantity < product.stock) {
                            product.quantity++;
                            updateBill();
                        } else if (product) {
                            alert("Insufficient stock!");
                        }
                    });
                });

                document.querySelectorAll('.decrease').forEach(button => {
                    button.addEventListener('click', function() {
                        const productId = this.getAttribute('data-id');
                        const product = bill.find(item => item.code === productId);
                        if (product && product.quantity > 1) {
                            product.quantity--;
                            updateBill();
                        }
                    });
                });
            }

            document.getElementById('generate-bill').addEventListener('click', function() {
                const customerName = document.getElementById('customer-name').value;
                const customerPhone = document.getElementById('customer-phone').value;
                const customerAddress = document.getElementById('customer-address').value;
                const paymentOption = document.getElementById('payment-option').value;

                if (customerName && customerPhone && customerAddress && paymentOption && bill.length > 0) {
                    let total = 0;
                    let productNames = [];
                    bill.forEach(item => {
                        total += item.price * item.quantity;
                        productNames.push(item.name);
                    });

                    const customerData = {
                        name: customerName,
                        phone: customerPhone,
                        address: customerAddress,
                        payment_option: paymentOption,
                        total: total,
                        products: productNames
                    };

                    fetch('store_customer.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(customerData),
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                console.log('Customer details saved');
                            } else {
                                console.error('Error saving customer details');
                            }
                        });

                    bill.forEach(item => {
                        const data = {
                            table_name: item.table_name,
                            code: item.code,
                            new_stock: item.stock - item.quantity,
                        };

                        console.log('Stock Update Data:', data);
                        fetch('update_stock.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify(data),
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    console.log(`Stock updated for ${item.name}`);
                                } else {
                                    console.error(`Error updating stock for ${item.name}: ${result.error}`);
                                }
                            })
                            .catch(error => console.error('Error:', error));
                    });
                    const billFormat = `
            <h2>WOOD FURNITURE</h2>
            <p>123 Furniture Street, Your City, Country</p>
            <hr>
            <table style="margin:5px 5px">
            <tr><th><h3>Bill Details:</h3></th></tr>
            <tr><td><p>Customer:</td><td> ${customerName}</p></td></tr>
            <tr><td><p>Phone:</td><td> ${customerPhone}</p></td></tr>
            <tr><td><p>Address:</td><td> ${customerAddress}</p></td></tr>
            <tr><td><p>Payment Option:</td><td> ${paymentOption}</p></td></tr>
            </table>

            <table border="1" cellspacing="0" cellpadding="8" width="500px" style="margin:20px 5px">
                <tr><th>Product Name</th><th>Price</th><th>Quantity</th><th>Total</th></tr>
                ${bill.map(item => `
                    <tr>
                        <td>${item.name}</td>
                        <td>₹${item.price}</td>
                        <td>${item.quantity}</td>
                        <td>₹${item.price * item.quantity}</td>
                    </tr>
                `).join('')}
                <tr><td colspan="3" style="text-align: right;">Total</td><td>₹${total}</td></tr>
            </table>
            <button onclick="window.print()">Print Bill</button> `;

                    const printWindow = window.open('', '', 'height=600,width=900');
                    printWindow.document.write(billFormat);
                    printWindow.document.close();

                } else {
                    alert("Please fill in all the customer details and select products!");
                }
                window.location.reload();
            });

            function searchProducts() {
                let input = document.getElementById('search-box').value.toLowerCase();
                let products = document.querySelectorAll('.product-item');

                products.forEach(product => {
                    let name = product.querySelector('h3').textContent.toLowerCase();
                    let id = product.getAttribute('data-id').toLowerCase();
                    let category = product.getAttribute('data-category').toLowerCase();

                    if (name.includes(input) || id.includes(input) || category.includes(input)) {
                        product.style.display = '';
                    } else {
                        product.style.display = 'none';
                    }
                });
            }
        </script>
    </center>
</body>

</html>