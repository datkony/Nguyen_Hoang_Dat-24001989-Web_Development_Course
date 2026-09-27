<?php
function lineDown(int $numOfLine) {
    for ($i = 0; $i < $numOfLine; $i++) {
        echo "<br>";
    }
}

echo "Bài 1";
lineDown(2);

Class CartItem {
    private string $name;
    private  float $price;
    private float $quantity;

    public function __construct(string $name, float $price, int $quantity) {
        if ($price <= 0) {
            throw new InvalidArgumentException("Giá mặt hàng phải lớn hơn 0");
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException("Số lượng mua phải lớn hơn 0");
        }

        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function getTotal() : float {
        return $this->price * $this->quantity;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setQuantity(int $quantity) {
        if ($quantity < 1) {
            throw new InvalidArgumentException("Số lượng mua phải lớn hơn hoặc bằng 1");
        }

        $this->quantity = $quantity;
    }

    public function getInfo() : string {
        return "[Tên sản phẩm: ". $this->name .
                ", Đơn giá: ". number_format($this->price, 2, '.', ',') ." (Đồng)".
                ", Số lượng: ". number_format($this->quantity, thousands_separator: ",") .
                ", Thành tiền: ". number_format($this->getTotal(), 2, '.', ',') ." (Đồng)".
                "]";
    }
}

Class ShoppingCart {
    /**
     * List of CartItems
     * @var CartItem[] $itemList
     */
    private $itemList;
    private string $id;

    public function __construct(string $id) {
        $this->id = $id;
        $this->itemList = [];

        echo "Đã khởi tạo giỏ hàng thành công!".
            " Mã giỏ hàng: ". $this->id ."<br>";
    }

    public function addItem(CartItem $item) {
        $searchItem = function($name, $item_list) {
            for ($i = 0; $i < count($item_list); $i++) {
                if ($item_list[$i]->getName() == $name) {
                    return $i;
                }
            }

            return false;
        };

        if ($searchItem($item->getName(), $this->itemList) !== false) {
            throw new UnexpectedValueException("Mặt hàng đã tồn tại.<br>");
        } else {
            array_push($this->itemList, $item);
            echo "Đã thêm thành công mặt hàng: ". $item->getInfo() ."<br>";
        }
    }

    public function removeItem(string $name) {
        $searchItem = function($name, $item_list) {
            for ($i = 0; $i < count($item_list); $i++) {
                if ($item_list[$i]->getName() == $name) {
                    return $i;
                }
            }

            return false;
        };

        $key = $searchItem($name, $this->itemList);

        if ($key !== false) {
            unset($this->item_list[$key]);
            echo "Đã xóa thành công mặt hàng: ". $name ."<br>";
        } else {
            throw new UnexpectedValueException("Không tìm thấy mặt hàng!<br>");
        }
    }

    public function calculateTotal() : float {
        $totalPrice = 0;

        foreach ($this->itemList as $item) {
            $totalPrice += $item->getTotal();
        }

        return $totalPrice;
    }

    public function displayCart() {
        $displayItemList = function($item_list) : string {
            $itemListString = "";

            foreach ($item_list as $item) {
                $itemListString .= ("<br>". $item->getInfo());
            }

            return $itemListString;
        };

        echo "Thông tin giỏ hàng:".
        "<br>Mã giỏ hàng: ". $this->id .
        "<br>Danh sách hàng: ". $displayItemList($this->itemList) .
        "<br>Tổng tiền: ". $this->calculateTotal() ." (Đồng)" ;
    }
}

$myShoppingCart = new ShoppingCart("3a671hf5");

function createAndAddItem(string $name, float $price, int $quantity) {
    try {
        $myItem = new CartItem($name, $price, $quantity);
        global $myShoppingCart;
        $myShoppingCart->addItem($myItem);
    } catch (Exception $e) {
        echo "". $e->getMessage() ."";
    }
}

function removingItem(string $name) {
    global $myShoppingCart;

    try {
        $myShoppingCart->removeItem($name);
    } catch (Exception $e) {
        echo "". $e->getMessage() ."";
    } finally {
        lineDown(1);
        $myShoppingCart->displayCart();
    }
}

createAndAddItem("Gạo", 45511.46, 2);
createAndAddItem("Thịt", 38137.88, 4);
createAndAddItem("Trứng", 17150.69, 7);
createAndAddItem("Tôm", 22104.7, 6);
createAndAddItem("Bí đỏ",34712,5);
createAndAddItem("Thịt", 39244.5, 3);

lineDown(1);
$myShoppingCart->displayCart();

lineDown(2);

removingItem("Cá");

lineDown(2);

removingItem("Thịt");

?>