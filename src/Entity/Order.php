<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use App\Enum\OrderStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: OrderStatusEnum::class)]
    private ?OrderStatusEnum $status = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $order_date = null;

    #[ORM\Column]
    private ?float $total_amount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToOne(inversedBy: 'order')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cart $cart = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', orphanRemoval: true)]
    private Collection $orderItems;

    /**
     * @var Collection<int, OrderStatusHistory>
     */
    #[ORM\OneToMany(targetEntity: OrderStatusHistory::class, mappedBy: 'order', orphanRemoval: true)]
    private Collection $orderStatusHistories;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->orderStatusHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?OrderStatusEnum
    {
        return $this->status;
    }

    public function setStatus(?OrderStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->order_date;
    }

    public function setOrderDate(\DateTimeInterface $order_date): static
    {
        $this->order_date = $order_date;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->total_amount;
    }

    public function setTotalAmount(float $total_amount): static
    {
        $this->total_amount = $total_amount;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): static
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderStatusHistory>
     */
    public function getOrderStatusHistories(): Collection
    {
        return $this->orderStatusHistories;
    }

    public function addOrderStatusHistory(OrderStatusHistory $orderStatusHistory): static
    {
        if (!$this->orderStatusHistories->contains($orderStatusHistory)) {
            $this->orderStatusHistories->add($orderStatusHistory);
            $orderStatusHistory->setOrder($this);
        }

        return $this;
    }

    public function removeOrderStatusHistory(OrderStatusHistory $orderStatusHistory): static
    {
        if ($this->orderStatusHistories->removeElement($orderStatusHistory)) {
            if ($orderStatusHistory->getOrder() === $this) {
                $orderStatusHistory->setOrder(null);
            }
        }

        return $this;
    }
}
